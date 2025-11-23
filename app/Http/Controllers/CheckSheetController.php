<?php

namespace App\Http\Controllers;

use App\Models\CheckSheetApproval;
use App\Models\CheckSheet;
use App\Models\CheckSheetSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CheckSheetController extends Controller
{
    // =========================
    // ROLE MATRIX
    // =========================
    private array $rolesManage  = ['admin','produksi','qa','logistik'];   // boleh bikin/edit/publish form
    private array $rolesReview  = ['admin','qa','logistik'];             // boleh approve/reject submission
    private array $rolesViewSub = ['admin','produksi','qa','logistik'];  // boleh lihat submissions
    private array $rolesFill    = ['operator'];                          // yang boleh isi

    // =========================
    // LIST FORM
    // =========================
    public function index(Request $request)
    {
        $query = CheckSheet::query()->latest();

        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $query->where('title', 'like', "%{$keyword}%");
        }

        if ($request->filled('department')) {
            $dept = trim($request->department);
            $query->where('department', 'like', "%{$dept}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

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
        $this->authorizeManage();
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
            'fields'      => ['nullable','array'],

            // status boleh dari UI
            'status'      => ['nullable','in:draft,active'],
        ]);

        $data['created_by'] = auth()->id();

        // Default meta
        $data['meta'] = [
            'approval_flow' => [
                'required' => 3, // harus 3 orang approve
                'roles' => ['admin', 'qa', 'logistik'], // urutan bebas
            ],
        ];


        // default draft kalau UI gak ngirim status
        $requestedStatus = $data['status'] ?? 'draft';

        // kalau minta active, hanya role manage yang boleh
        if ($requestedStatus === 'active' && $this->canPublish()) {
            $data['status']       = 'active';
            $data['published_by'] = auth()->id();
            $data['published_at'] = now();
        } else {
            $data['status'] = 'draft';
        }

        $form = CheckSheet::create($data);

        return redirect()
            ->route('check_sheets.edit', $form)
            ->with('success', 'Form Check Sheet berhasil dibuat.');
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
            'status'      => ['nullable','in:draft,active'],
        ]);

        // kalau active terus diedit DAN kamu mau compliance publish ulang:
        if ($checkSheet->status === 'active' && empty($data['status'])) {
            $data['status'] = 'draft';
        }

        // kalau UI minta active lagi (publish ulang)
        if (($data['status'] ?? null) === 'active' && $this->canPublish()) {
            $data['published_by'] = auth()->id();
            $data['published_at'] = now();
        }
        // Kalau meta tidak dikirim dari UI, pertahankan meta lama
        if (!isset($data['meta'])) {
            $data['meta'] = $checkSheet->meta;
        }

        $checkSheet->update($data);

        return redirect()
            ->route('check_sheets.edit', $checkSheet)
            ->with('success', 'Form berhasil diupdate.');
    }

    // =========================
    // DELETE FORM
    // =========================
    public function destroy(CheckSheet $checkSheet)
    {
        if (!auth()->user()->isRole(['admin'])) {
            return back()->with('error', 'Hanya admin yang boleh menghapus form.');
        }

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
        $checkSheet->published_by = auth()->id();
        $checkSheet->published_at = now();
        $checkSheet->save();

        return back()->with('success', 'Form berhasil di-Publish dan siap dipakai operator.');
    }

    public function unpublish(CheckSheet $checkSheet)
    {
        $this->authorizeManage();

        $checkSheet->status = 'draft';
        $checkSheet->save();

        return back()->with('success', 'Form di-Unpublish (Draft).');
    }

    // =========================
    // GENERATE QR FORM
    // =========================
    public function generateQr(CheckSheet $checkSheet)
    {
        $this->authorizeManage();

        $url = route('check_sheets.fill', $checkSheet);

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

        $checkSheet->qr_path = $qrPath;
        $checkSheet->qr_url  = $qrUrl;
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

        // kalau mau batasi hanya operator:
        // abort_unless(auth()->user()->isRole($this->rolesFill), 403);

        $basic = $request->validate([
            'shift'  => ['required','string','max:50'],
            'result' => ['required','string'],
            'notes'  => ['nullable','string'],
        ]);

        $dynamic = $request->input('data', []);
        if (!is_array($dynamic)) $dynamic = [];

        $payload = array_merge($dynamic, $basic);

        CheckSheetSubmission::create([
            'check_sheet_id' => $checkSheet->id,
            'operator_id'    => auth()->id(),
            'status'         => 'under_review',
            'data'           => $payload,
            'submitted_at'   => now(),
        ]);

        return redirect()
            ->route('check_sheets.submissions')
            ->with('success', 'Check Sheet berhasil dikirim & menunggu approval.');
    }

    // =========================
    // LIST SUBMISSIONS
    // =========================
    public function submissions(Request $request)
    {
        $this->authorizeViewSubmissions();

        $query = CheckSheetSubmission::with([
                'checkSheet',
                'operator',
                'reviewer',
                'approvals.reviewer',   // 👈 penting: load semua approval + usernya
            ])
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

    public function showSubmission(CheckSheetSubmission $submission)
    {
        $this->authorizeViewSubmissions();

        $submission->load(['checkSheet','operator','reviewer']);

        return view('check_sheets.submissions_show', compact('submission'));
    }

    // =========================
    // APPROVAL QA / LOGISTIK
    // =========================
    public function updateStatus(Request $request, CheckSheetSubmission $submission)
    {
        $this->authorizeReview();

        // normalisasi input dari UI
        $raw    = $request->input('status');
        $status = strtolower(trim((string) $raw));

        if ($status === 'approve') $status = 'approved';
        if ($status === 'reject')  $status = 'rejected';

        $request->merge(['status' => $status]);

        $data = $request->validate([
            'status' => ['required', Rule::in(['under_review','approved','rejected'])],
            'note'   => ['nullable','string'],
        ], [
            'status.in' => 'Status tidak valid.',
        ]);

        $user = auth()->user();

        // 1) Kalau cuma mau ubah jadi "UNDER REVIEW" saja
        if ($data['status'] === 'under_review') {
            $submission->status      = 'under_review';
            $submission->reviewed_by = $user->id;
            $submission->reviewed_at = now();
            $submission->save();

            return back()->with('success', 'Status diubah ke UNDER REVIEW.');
        }

        // 2) APPROVED / REJECTED -> catat ke tabel approvals
        CheckSheetApproval::updateOrCreate(
            [
                'check_sheet_submission_id' => $submission->id,
                'reviewer_id'               => $user->id,
            ],
            [
                'status' => $data['status'] === 'approved' ? 'approved' : 'rejected',
                'note'   => $data['note'] ?? null,
            ]
        );

        // 3) Baca aturan dari meta form
        $meta = $submission->checkSheet->meta ?? [];
        $flow = $meta['approval_flow'] ?? [];

        $required = (int)($flow['required'] ?? 1);
        if ($required < 1) $required = 1;

        // Hitung approval & reject yang sudah masuk
        $approvedCount = $submission->approvals()
            ->where('status', 'approved')
            ->count();

        $rejectedCount = $submission->approvals()
            ->where('status', 'rejected')
            ->count();

        // 4) Tentukan status akhir submission
        if ($rejectedCount > 0 || $data['status'] === 'rejected') {
            // Ada yang nolak: final REJECTED
            $submission->status      = 'rejected';
            $submission->reviewed_by = $user->id;
            $submission->reviewed_at = now();

        } elseif ($approvedCount >= $required) {
            // Sudah memenuhi jumlah approval
            $submission->status      = 'approved';
            $submission->reviewed_by = $user->id;   // approver terakhir
            $submission->reviewed_at = now();

        } else {
            // Belum cukup approve, masih proses
            $submission->status      = 'under_review';
            $submission->reviewed_by = $user->id;
            $submission->reviewed_at = now();
        }

        $submission->save();

        return back()->with('success', 'Approval berhasil dicatat.');
    }


    // =========================
    // HELPER
    // =========================
    private function authorizeManage()
    {
        if (!auth()->user()->isRole($this->rolesManage)) {
            abort(403, 'Anda tidak punya akses mengelola form.');
        }
    }

    private function authorizeViewSubmissions()
    {
        if (!auth()->user()->isRole($this->rolesViewSub)) {
            abort(403, 'Anda tidak punya akses melihat submission.');
        }
    }

    private function authorizeReview()
    {
        if (!auth()->user()->isRole($this->rolesReview)) {
            abort(403, 'Anda tidak punya akses approval.');
        }
    }

    private function canPublish(): bool
    {
        return auth()->user()->isRole($this->rolesManage);
    }
}
