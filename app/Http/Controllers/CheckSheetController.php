<?php

namespace App\Http\Controllers;

use App\Models\CheckSheet;
use App\Models\CheckSheetSubmission;
use Illuminate\Http\Request;

class CheckSheetController extends Controller
{
    public function index(Request $request)
    {
        $q = CheckSheet::query()->orderByDesc('created_at');

        if ($request->filled('department')) {
            $q->where('department', $request->department);
        }

        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }

        $forms = $q->paginate(10)->withQueryString();

        return view('check_sheets.index', compact('forms'));
    }

    public function create()
    {
        return view('check_sheets.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'      => ['required','string','max:255'],
            'department' => ['required','string','max:100'],
            'product'    => ['nullable','string','max:100'],
            'line'       => ['nullable','string','max:100'],
            'description'=> ['nullable','string'],
        ]);

        $data['status'] = 'active';
        $data['created_by'] = auth()->id();

        CheckSheet::create($data);

        return redirect()->route('check_sheets.index')->with('success', 'Check Sheet berhasil dibuat.');
    }

    // Operator isi form
    public function fill(CheckSheet $checkSheet)
    {
        if ($checkSheet->status !== 'active') {
            abort(404);
        }

        return view('check_sheets.fill', compact('checkSheet'));
    }

    public function submit(Request $request, CheckSheet $checkSheet)
    {
        if ($checkSheet->status !== 'active') {
            abort(404);
        }

        $data = $request->validate([
            'shift' => ['required','string','max:50'],
            'result' => ['required','string'],
            'notes' => ['nullable','string'],
        ]);

        CheckSheetSubmission::create([
            'check_sheet_id' => $checkSheet->id,
            'operator_id'    => auth()->id(),
            'status'         => 'submitted',
            'data'           => $data,
            'submitted_at'   => now(),
        ]);

        return redirect()->route('check_sheets.submissions')->with('success', 'Check sheet submitted.');
    }

    // List semua submission
    public function submissions(Request $request)
    {
        $q = CheckSheetSubmission::with(['checkSheet','operator'])
            ->orderByDesc('submitted_at');

        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }

        $submissions = $q->paginate(15)->withQueryString();

        return view('check_sheets.submissions', compact('submissions'));
    }

    // Approval QA / Logistik
    public function updateStatus(Request $request, CheckSheetSubmission $submission)
    {
        $data = $request->validate([
            'status' => ['required','in:under_review,approved,rejected'],
        ]);

        $submission->status = $data['status'];
        $submission->reviewed_by = auth()->id();
        $submission->reviewed_at = now();
        $submission->save();

        return back()->with('success', 'Status submission diperbarui.');
    }
}
