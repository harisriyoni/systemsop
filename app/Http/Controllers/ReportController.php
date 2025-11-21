<?php

namespace App\Http\Controllers;

use App\Models\Sop;
use App\Models\CheckSheet;
use App\Models\CheckSheetSubmission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Dashboard report utama.
     * Filter:
     * - from (YYYY-MM-DD)
     * - to (YYYY-MM-DD)
     * - department (string, optional)
     */
    public function index(Request $request)
    {
        // ===== RANGE TANGGAL DEFAULT 30 HARI =====
        $from = $request->filled('from')
            ? Carbon::parse($request->from)->startOfDay()
            : now()->subDays(30)->startOfDay();

        $to = $request->filled('to')
            ? Carbon::parse($request->to)->endOfDay()
            : now()->endOfDay();

        $department = $request->filled('department') ? trim($request->department) : null;

        // =========================================================
        // SOP REPORT
        // =========================================================
        $sopBase = Sop::query()->whereBetween('created_at', [$from, $to]);
        if ($department) {
            $sopBase->where('department', 'like', "%{$department}%");
        }

        $sopTotal = (clone $sopBase)->count();

        $sopByStatus = (clone $sopBase)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $sopPublicStats = (clone $sopBase)
            ->selectRaw("
                SUM(CASE WHEN is_public = 1 THEN 1 ELSE 0 END) as total_public,
                SUM(CASE WHEN (is_public = 0 OR is_public IS NULL) THEN 1 ELSE 0 END) as total_private
            ")
            ->first();

        $sopPendingApproval = (clone $sopBase)
            ->where('status', 'waiting_approval')
            ->selectRaw("
                COUNT(*) as total_waiting,
                SUM(CASE WHEN is_approved_produksi = 0 THEN 1 ELSE 0 END) as produksi_pending,
                SUM(CASE WHEN is_approved_qa = 0 THEN 1 ELSE 0 END) as qa_pending,
                SUM(CASE WHEN is_approved_logistik = 0 THEN 1 ELSE 0 END) as logistik_pending
            ")
            ->first();

        // trend SOP per hari (created)
        $sopPerDay = (clone $sopBase)
            ->selectRaw("DATE(created_at) as day, COUNT(*) as total")
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // =========================================================
        // CHECK SHEET FORMS REPORT
        // =========================================================
        $formBase = CheckSheet::query()->whereBetween('created_at', [$from, $to]);
        if ($department) {
            $formBase->where('department', 'like', "%{$department}%");
        }

        $formTotal = (clone $formBase)->count();

        $formByStatus = (clone $formBase)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $formByDept = (clone $formBase)
            ->select('department', DB::raw('COUNT(*) as total'))
            ->groupBy('department')
            ->orderByDesc('total')
            ->pluck('total', 'department')
            ->toArray();

        // =========================================================
        // SUBMISSIONS REPORT
        // =========================================================
        $subBase = CheckSheetSubmission::query()
            ->whereNotNull('submitted_at')
            ->whereBetween('submitted_at', [$from, $to]);

        if ($department) {
            $subBase->whereHas('checkSheet', function ($q) use ($department) {
                $q->where('department', 'like', "%{$department}%");
            });
        }

        $subTotal = (clone $subBase)->count();

        $subByStatus = (clone $subBase)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // trend submissions per hari
        $subPerDay = (clone $subBase)
            ->selectRaw("DATE(submitted_at) as day, COUNT(*) as total")
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // top forms (yang paling sering disubmit)
        $topForms = (clone $subBase)
            ->selectRaw("check_sheet_id, COUNT(*) as total")
            ->groupBy('check_sheet_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                $form = CheckSheet::find($row->check_sheet_id);
                return [
                    'id'    => $row->check_sheet_id,
                    'title' => $form->title ?? 'Unknown Form',
                    'dept'  => $form->department ?? '-',
                    'total' => (int) $row->total,
                ];
            });

        // top operators
        $topOperatorsRaw = (clone $subBase)
            ->selectRaw("operator_id, COUNT(*) as total")
            ->groupBy('operator_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $operatorIds = $topOperatorsRaw->pluck('operator_id')->filter()->unique()->values();
        $operators = User::whereIn('id', $operatorIds)->pluck('name', 'id');

        $topOperators = $topOperatorsRaw->map(function ($row) use ($operators) {
            return [
                'id'    => $row->operator_id,
                'name'  => $operators[$row->operator_id] ?? 'Unknown',
                'total' => (int) $row->total,
            ];
        });

        // =========================================================
        // KIRIM KE VIEW
        // =========================================================
        return view('reports.index', [
            'from' => $from,
            'to' => $to,
            'department' => $department,

            // SOP
            'sopTotal' => $sopTotal,
            'sopByStatus' => $sopByStatus,
            'sopPublicStats' => $sopPublicStats,
            'sopPendingApproval' => $sopPendingApproval,
            'sopPerDay' => $sopPerDay,

            // Forms
            'formTotal' => $formTotal,
            'formByStatus' => $formByStatus,
            'formByDept' => $formByDept,

            // Submissions
            'subTotal' => $subTotal,
            'subByStatus' => $subByStatus,
            'subPerDay' => $subPerDay,
            'topForms' => $topForms,
            'topOperators' => $topOperators,
        ]);
    }

    /**
     * OPTIONAL: Export submissions CSV (kalau nanti kamu butuh download).
     * Route contoh:
     * Route::get('/reports/submissions/export', [ReportController::class,'exportSubmissionsCsv'])->name('reports.submissions.export');
     */
    public function exportSubmissionsCsv(Request $request)
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->from)->startOfDay()
            : now()->subDays(30)->startOfDay();

        $to = $request->filled('to')
            ? Carbon::parse($request->to)->endOfDay()
            : now()->endOfDay();

        $department = $request->filled('department') ? trim($request->department) : null;

        $q = CheckSheetSubmission::with(['checkSheet','operator','reviewer'])
            ->whereNotNull('submitted_at')
            ->whereBetween('submitted_at', [$from, $to])
            ->orderByDesc('submitted_at');

        if ($department) {
            $q->whereHas('checkSheet', function ($sub) use ($department) {
                $sub->where('department', 'like', "%{$department}%");
            });
        }

        $rows = $q->get();

        $filename = "checksheet_submissions_{$from->format('Ymd')}_{$to->format('Ymd')}.csv";
        $headers = [
            "Content-Type"        => "text/csv",
            "Content-Disposition" => "attachment; filename={$filename}",
        ];

        $columns = [
            'Submitted At',
            'Form Title',
            'Department',
            'Operator',
            'Shift',
            'Result',
            'Notes',
            'Status',
            'Reviewer',
            'Reviewed At',
        ];

        $callback = function () use ($rows, $columns) {
            $fh = fopen('php://output', 'w');
            fputcsv($fh, $columns);

            foreach ($rows as $r) {
                $data = is_array($r->data) ? $r->data : (json_decode($r->data, true) ?: []);
                fputcsv($fh, [
                    optional($r->submitted_at)->format('Y-m-d H:i:s'),
                    $r->checkSheet->title ?? '-',
                    $r->checkSheet->department ?? '-',
                    $r->operator->name ?? '-',
                    $data['shift'] ?? '-',
                    str_replace(["\r","\n"], ' | ', $data['result'] ?? '-'),
                    $data['notes'] ?? '-',
                    $r->status,
                    $r->reviewer->name ?? '-',
                    optional($r->reviewed_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($fh);
        };

        return response()->stream($callback, 200, $headers);
    }
}
