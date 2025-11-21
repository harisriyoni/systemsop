<?php

namespace App\Http\Controllers;

use App\Models\CheckSheet;
use App\Models\CheckSheetSubmission;
use Illuminate\Http\Request;

class CheckSheetController extends Controller
{
    public function index(Request $request)
    {
        $query = CheckSheet::query()->latest(); // orderByDesc(created_at)

        // ===== SEARCH (title) =====
        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $query->where('title', 'like', "%{$keyword}%");
        }

        // ===== FILTER DEPARTEMEN (partial match biar fleksibel) =====
        if ($request->filled('department')) {
            $dept = trim($request->department);
            $query->where('department', 'like', "%{$dept}%");
        }

        // ===== FILTER STATUS =====
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // ===== FILTER PRODUK / LINE (opsional sesuai UI) =====
        if ($request->filled('product')) {
            $query->where('product', 'like', "%".trim($request->product)."%");
        }

        if ($request->filled('line')) {
            $query->where('line', 'like', "%".trim($request->line)."%");
        }

        $forms = $query->paginate(10)->withQueryString();

        return view('check_sheets.index', compact('forms'));
    }

    public function create()
    {
        // kalau suatu saat mau halaman create terpisah
        return view('check_sheets.create');
    }

    public function store(Request $request)
    {
        // role yang boleh bikin form (sesuaikan kalau perlu)
        if (!auth()->user()->isRole(['admin','produksi','qa','logistik'])) {
            return back()->with('error', 'Anda tidak punya akses membuat form.');
        }

        $data = $request->validate([
            'title'       => ['required','string','max:255'],
            'department'  => ['required','string','max:100'],
            'product'     => ['nullable','string','max:100'],
            'line'        => ['nullable','string','max:100'],
            'description' => ['nullable','string'],
        ]);

        // default status sesuai flow kamu
        $data['status']     = 'active'; 
        // kalau mau draft dulu:
        // $data['status']  = 'draft';

        $data['created_by'] = auth()->id();

        CheckSheet::create($data);

        return redirect()
            ->route('check_sheets.index')
            ->with('success', 'Form Check Sheet berhasil dibuat.');
    }

    // Operator isi form
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

        $data = $request->validate([
            'shift'  => ['required','string','max:50'],
            'result' => ['required','string'],
            'notes'  => ['nullable','string'],
        ]);

        CheckSheetSubmission::create([
            'check_sheet_id' => $checkSheet->id,
            'operator_id'    => auth()->id(),
            'status'         => 'submitted',
            'data'           => $data,
            'submitted_at'   => now(),
        ]);

        return redirect()
            ->route('check_sheets.submissions')
            ->with('success', 'Check Sheet berhasil dikirim.');
    }

    // List semua submission
    public function submissions(Request $request)
    {
        $query = CheckSheetSubmission::with(['checkSheet','operator'])
            ->orderByDesc('submitted_at');

        // filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // search opsional: judul form / nama operator
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

    // Approval QA / Logistik / Admin
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
}
