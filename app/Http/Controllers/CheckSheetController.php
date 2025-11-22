<?php

namespace App\Http\Controllers;

use App\Models\CheckSheet;
use App\Models\CheckSheetSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CheckSheetController extends Controller
{
    // =========================
    // LIST FORM
    // =========================
    public function index(Request $request)
    {
        $query = CheckSheet::query()->latest();

        // SEARCH title
        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $query->where('title', 'like', "%{$keyword}%");
        }

        // FILTER DEPT
        if ($request->filled('department')) {
            $dept = trim($request->department);
            $query->where('department', 'like', "%{$dept}%");
        }

        // FILTER STATUS
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // FILTER PRODUCT / LINE
        if ($request->filled('product')) {
            $query->where('product', 'like', "%".trim($request->product)."%");
        }

        if ($request->filled('line')) {
            $query->where('line', 'like', "%".trim($request->line)."%");
        }

        $forms = $query->paginate(10)->withQueryString();

        return view('check_sheets.index', compact('forms'));
    }

    // =========================
    // CREATE FORM
    // =========================
    public function create()
    {
        return view('check_sheets.create');
    }

    public function store(Request $request)
    {
        $this->authorizeManage();

        $data = $request->validate([
            'title'       => ['required','string','max:255'],
            'department'  => ['required','string','max:100'],
            'product'     => ['nullable','string','max:100'],
            'line'        => ['nullable','string','max:100'],
            'description' => ['nullable','string'],

            // OPTIONAL kalau builder kamu simpan field JSON
            'fields'      => ['nullable','array'],
        ]);

        // default status: draft dulu biar sesuai flow publish
        $data['status']     = 'draft';
        $data['created_by'] = auth()->id();

        $form = CheckSheet::create($data);

        return redirect()
            ->route('check_sheets.edit', $form)
            ->with('success', 'Form Check Sheet berhasil dibuat (Draft). Silakan Publish jika sudah final.');
    }

    // =========================
    // EDIT / UPDATE FORM
    // =========================
    public function edit(CheckSheet $checkSheet)
    {
        $this->authorizeManage();
        return view('check_sheets.edit', compact('checkSheet'));
    }

    public function update(Request $request, CheckSheet $checkSheet)
    {
        $this->authorizeManage();

        $data = $request->validate([
            'title'       => ['required','string','max:255'],
            'department'  => ['required','string','max:100'],
            'product'     => ['nullable','string','max:100'],
            'line'        => ['nullable','string','max:100'],
            'description' => ['nullable','string'],
            'fields'      => ['nullable','array'],
        ]);

        // kalau sudah aktif lalu diedit → balik jadi draft biar publish ulang (compliance)
        if ($checkSheet->status === 'active') {
            $data['status'] = 'draft';
        }

        $checkSheet->update($data);

        return redirect()
            ->route('check_sheets.edit', $checkSheet)
            ->with('success', 'Form berhasil diupdate. Jika tadi Active, sekarang jadi Draft dan perlu Publish ulang.');
    }

    // =========================
    // DELETE FORM
    // =========================
    public function destroy(CheckSheet $checkSheet)
    {
        if (!auth()->user()->isRole(['admin'])) {
            return back()->with('error', 'Hanya admin yang boleh menghapus form.');
        }

        // optional: hapus submissions juga kalau mau hard delete
        // $checkSheet->submissions()->delete();

        $checkSheet->delete();

        return redirect()
            ->route('check_sheets.index')
            ->with('success', 'Form Check Sheet berhasil dihapus.');
    }

    // =========================
    // PUBLISH / UNPUBLISH
    // =========================
    public function publish(CheckSheet $checkSheet)
    {
        $this->authorizeManage();

        $checkSheet->status       = 'active';
        $checkSheet->published_by = auth()->id();   // kolom opsional
        $checkSheet->published_at = now();          // kolom opsional
        $checkSheet->save();

        return back()->with('success', 'Form berhasil di-Publish dan siap dipakai operator.');
    }

    public function unpublish(CheckSheet $checkSheet)
    {
        $this->authorizeManage();

        $checkSheet->status = 'draft';
        $checkSheet->save();

        return back()->with('success', 'Form di-Unpublish (Draft). Operator tidak bisa isi sebelum Publish lagi.');
    }

    // =========================
    // GENERATE QR FORM
    // =========================
    public function generateQr(CheckSheet $checkSheet)
    {
        $this->authorizeManage();

        // URL isi form (via QR)
        $url = route('check_sheets.fill', $checkSheet);

        // Simpan QR kalau package simple-qrcode ada, kalau tidak ya simpan url-nya aja
        $qrPath = null;
        $qrUrl  = $url;

        if (class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class)) {
            $fileName = 'qr-checksheet-'.$checkSheet->id.'-'.Str::random(6).'.png';
            $qrPath   = 'qr/'.$fileName;

            $png = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                ->size(600)
                ->margin(2)
                ->generate($url);

            Storage::disk('public')->put($qrPath, $png);
            $qrUrl = Storage::disk('public')->url($qrPath);
        }

        $checkSheet->qr_path = $qrPath; // kolom opsional
        $checkSheet->qr_url  = $qrUrl;  // kolom opsional
        $checkSheet->save();

        return back()->with('success', 'QR Form berhasil dibuat.');
    }

    // =========================
    // OPERATOR FILL FORM
    // =========================
    public function fill(CheckSheet $checkSheet)
    {
        if ($checkSheet->status !== 'active') {
            abort(404, 'Form tidak aktif.');
        }

        return view('check_sheets.fill', compact('checkSheet'));
    }

    public function submit(Request $request, CheckSheet $checkSheet)
    {
        if ($checkSheet->status !== 'active') {
            abort(404, 'Form tidak aktif.');
        }

        // Validasi basic (biar demo aman)
        $basic = $request->validate([
            'shift'  => ['required','string','max:50'],
            'result' => ['required','string'],
            'notes'  => ['nullable','string'],
        ]);

        // Support field dynamic dari builder (optional)
        $dynamic = $request->input('data', []);
        if (!is_array($dynamic)) $dynamic = [];

        $payload = array_merge($dynamic, $basic);

        CheckSheetSubmission::create([
            'check_sheet_id' => $checkSheet->id,
            'operator_id'    => auth()->id(),
            'status'         => 'submitted',
            'data'           => $payload,
            'submitted_at'   => now(),
        ]);

        return redirect()
            ->route('check_sheets.submissions')
            ->with('success', 'Check Sheet berhasil dikirim.');
    }

    // =========================
    // LIST SUBMISSIONS
    // =========================
    public function submissions(Request $request)
    {
        $query = CheckSheetSubmission::with(['checkSheet','operator'])
            ->orderByDesc('submitted_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $query->where(function ($sub) use ($keyword) {
                $sub->whereHas('checkSheet', function ($q) use ($keyword) {
                        $q->where('title', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('operator', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
            });
        }

        $submissions = $query->paginate(15)->withQueryString();

        return view('check_sheets.submissions', compact('submissions'));
    }

    // DETAIL SUBMISSION (baru, buat route submissions.show)
    public function showSubmission(CheckSheetSubmission $submission)
    {
        $role = auth()->user()->role;
        if (!in_array($role, ['admin','produksi','qa','logistik'])) {
            abort(403);
        }

        $submission->load(['checkSheet','operator','reviewer']);

        return view('check_sheets.submissions_show', compact('submission'));
    }

    // =========================
    // APPROVAL QA / LOGISTIK
    // =========================
    public function updateStatus(Request $request, CheckSheetSubmission $submission)
    {
        $role = auth()->user()->role;

        if (!in_array($role, ['admin','qa','logistik'])) {
            return back()->with('error', 'Anda tidak punya akses untuk menyetujui submission.');
        }

        $data = $request->validate([
            'status' => ['required','in:under_review,approved,rejected'],
        ], [
            'status.in' => 'Status tidak valid.',
        ]);

        $submission->status      = $data['status'];
        $submission->reviewed_by = auth()->id();
        $submission->reviewed_at = now();
        $submission->save();

        return back()->with('success', 'Status submission diperbarui.');
    }

    // =========================
    // HELPER
    // =========================
    private function authorizeManage()
    {
        if (!auth()->user()->isRole(['admin','produksi','qa','logistik'])) {
            abort(403, 'Anda tidak punya akses mengelola form.');
        }
    }
}
