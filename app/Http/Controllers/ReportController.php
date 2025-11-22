<?php

namespace App\Http\Controllers;

use App\Models\Sop;
use App\Models\CheckSheet;
use App\Models\CheckSheetSubmission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportController extends Controller
{
    /**
     * Dashboard report utama (SOP + Forms + Submissions).
     * Filter:
     * - from (YYYY-MM-DD)
     * - to (YYYY-MM-DD)
     * - department (string, optional)
     * - product (string, optional)
     * - line (string, optional)
     * - status (string, optional) -> khusus submissions (optional)
     */
    public function index(Request $request)
    {
        // ===== RANGE TANGGAL DEFAULT 30 HARI (SAFE PARSE) =====
        $from = $request->filled('from')
            ? $this->safeParseDate($request->from, now()->subDays(30))->startOfDay()
            : now()->subDays(30)->startOfDay();

        $to = $request->filled('to')
            ? $this->safeParseDate($request->to, now())->endOfDay()
            : now()->endOfDay();

        if ($from->gt($to)) {
            // swap kalau user kebalik input
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $department = $request->filled('department') ? trim($request->department) : null;
        $product    = $request->filled('product') ? trim($request->product) : null;
        $line       = $request->filled('line') ? trim($request->line) : null;
        $subStatus  = $request->filled('status') ? trim($request->status) : null;

        // =========================================================
        // SOP REPORT
        // =========================================================
        $sopBase = Sop::query()->whereBetween('created_at', [$from, $to]);

        if ($department) $sopBase->where('department', 'like', "%{$department}%");
        if ($product)    $sopBase->where('product', 'like', "%{$product}%");
        if ($line)       $sopBase->where('line', 'like', "%{$line}%");

        $sopTotal = (clone $sopBase)->count();

        $sopByStatus = (clone $sopBase)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // pastiin status key lengkap biar view gak undefined
        $sopStatuses = ['draft','waiting_approval','approved','expired','rejected','archived'];
        foreach ($sopStatuses as $st) {
            $sopByStatus[$st] = $sopByStatus[$st] ?? 0;
        }

        // public vs private (safe kalau kolom belum ada)
        $sopPublicStats = (object)[
            'total_public' => 0,
            'total_private' => $sopTotal,
        ];

        if (Schema::hasColumn('sops', 'is_public')) {
            $sopPublicStats = (clone $sopBase)
                ->selectRaw("
                    SUM(CASE WHEN is_public = 1 THEN 1 ELSE 0 END) as total_public,
                    SUM(CASE WHEN (is_public = 0 OR is_public IS NULL) THEN 1 ELSE 0 END) as total_private
                ")
                ->first();
        }

        // pending approval per stage (safe columns)
        $sopPendingApproval = (object)[
            'total_waiting'     => 0,
            'produksi_pending'  => 0,
            'qa_pending'        => 0,
            'logistik_pending'  => 0,
        ];

        $hasProdCol = Schema::hasColumn('sops', 'is_approved_produksi');
        $hasQaCol   = Schema::hasColumn('sops', 'is_approved_qa');
        $hasLogCol  = Schema::hasColumn('sops', 'is_approved_logistik');

        if ($hasProdCol && $hasQaCol && $hasLogCol) {
            $sopPendingApproval = (clone $sopBase)
                ->where('status', 'waiting_approval')
                ->selectRaw("
                    COUNT(*) as total_waiting,
                    SUM(CASE WHEN is_approved_produksi = 0 THEN 1 ELSE 0 END) as produksi_pending,
                    SUM(CASE WHEN is_approved_qa = 0 THEN 1 ELSE 0 END) as qa_pending,
                    SUM(CASE WHEN is_approved_logistik = 0 THEN 1 ELSE 0 END) as logistik_pending
                ")
                ->first();
        } else {
            // fallback kalau belum punya kolom stage
            $tmp = (clone $sopBase)->where('status','waiting_approval')->count();
            $sopPendingApproval->total_waiting = $tmp;
        }

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

        if ($department) $formBase->where('department', 'like', "%{$department}%");
        if ($product)    $formBase->where('product', 'like', "%{$product}%");
        if ($line)       $formBase->where('line', 'like', "%{$line}%");

        $formTotal = (clone $formBase)->count();

        $formByStatus = (clone $formBase)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $formStatuses = ['draft','active','inactive','archived'];
        foreach ($formStatuses as $st) {
            $formByStatus[$st] = $formByStatus[$st] ?? 0;
        }

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

        if ($department || $product || $line) {
            $subBase->whereHas('checkSheet', function ($q) use ($department,$product,$line) {
                if ($department) $q->where('department', 'like', "%{$department}%");
                if ($product)    $q->where('product', 'like', "%{$product}%");
                if ($line)       $q->where('line', 'like', "%{$line}%");
            });
        }

        if ($subStatus) {
            $subBase->where('status', $subStatus);
        }

        $subTotal = (clone $subBase)->count();

        $subByStatus = (clone $subBase)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $subStatuses = ['submitted','under_review','approved','rejected'];
        foreach ($subStatuses as $st) {
            $subByStatus[$st] = $subByStatus[$st] ?? 0;
        }

        // trend submissions per hari
        $subPerDay = (clone $subBase)
            ->selectRaw("DATE(submitted_at) as day, COUNT(*) as total")
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // top forms (tanpa N+1)
        $topForms = (clone $subBase)
            ->join('check_sheets as cs', 'cs.id', '=', 'check_sheet_submissions.check_sheet_id')
            ->selectRaw("check_sheet_id, cs.title, cs.department, COUNT(*) as total")
            ->groupBy('check_sheet_id','cs.title','cs.department')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn($r) => [
                'id'    => $r->check_sheet_id,
                'title' => $r->title,
                'dept'  => $r->department ?? '-',
                'total' => (int) $r->total,
            ]);

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
            'product' => $product,
            'line' => $line,
            'status' => $subStatus,

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
     * Export submissions CSV.
     */
    public function exportSubmissionsCsv(Request $request)
    {
        $from = $request->filled('from')
            ? $this->safeParseDate($request->from, now()->subDays(30))->startOfDay()
            : now()->subDays(30)->startOfDay();

        $to = $request->filled('to')
            ? $this->safeParseDate($request->to, now())->endOfDay()
            : now()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $department = $request->filled('department') ? trim($request->department) : null;
        $product    = $request->filled('product') ? trim($request->product) : null;
        $line       = $request->filled('line') ? trim($request->line) : null;
        $status     = $request->filled('status') ? trim($request->status) : null;

        $q = CheckSheetSubmission::with(['checkSheet','operator','reviewer'])
            ->whereNotNull('submitted_at')
            ->whereBetween('submitted_at', [$from, $to])
            ->orderByDesc('submitted_at');

        if ($department || $product || $line) {
            $q->whereHas('checkSheet', function ($sub) use ($department,$product,$line) {
                if ($department) $sub->where('department', 'like', "%{$department}%");
                if ($product)    $sub->where('product', 'like', "%{$product}%");
                if ($line)       $sub->where('line', 'like', "%{$line}%");
            });
        }

        if ($status) {
            $q->where('status', $status);
        }

        $rows = $q->get();

        $filename = "checksheet_submissions_{$from->format('Ymd')}_{$to->format('Ymd')}.csv";
        $headers = [
            "Content-Type"        => "text/csv",
            "Content-Disposition" => "attachment; filename={$filename}",
        ];

        $columns = [
            'Submitted At',
            'Form Code',
            'Form Title',
            'Department',
            'Product',
            'Line',
            'Operator',
            'Shift',
            'Result',
            'Notes',
            'Status',
            'Reviewer',
            'Reviewed At',
            'Data JSON',
        ];

        $callback = function () use ($rows, $columns) {
            $fh = fopen('php://output', 'w');
            fputcsv($fh, $columns);

            foreach ($rows as $r) {
                $data = is_array($r->data) ? $r->data : (json_decode($r->data, true) ?: []);

                fputcsv($fh, [
                    optional($r->submitted_at)->format('Y-m-d H:i:s'),
                    $r->checkSheet->code ?? '-',                // kolom opsional
                    $r->checkSheet->title ?? '-',
                    $r->checkSheet->department ?? '-',
                    $r->checkSheet->product ?? '-',
                    $r->checkSheet->line ?? '-',
                    $r->operator->name ?? '-',
                    $data['shift'] ?? '-',
                    str_replace(["\r","\n"], ' | ', $data['result'] ?? '-'),
                    $data['notes'] ?? '-',
                    $r->status,
                    $r->reviewer->name ?? '-',
                    optional($r->reviewed_at)->format('Y-m-d H:i:s'),
                    json_encode($data, JSON_UNESCAPED_UNICODE),
                ]);
            }

            fclose($fh);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export SOP PDF (opsional route: reports.sop.export).
     * - Kalau DomPDF ada -> PDF
     * - Kalau tidak ada -> CSV fallback
     */
    public function exportSopPdf(Request $request)
    {
        $from = $request->filled('from')
            ? $this->safeParseDate($request->from, now()->subDays(30))->startOfDay()
            : now()->subDays(30)->startOfDay();

        $to = $request->filled('to')
            ? $this->safeParseDate($request->to, now())->endOfDay()
            : now()->endOfDay();

        $department = $request->filled('department') ? trim($request->department) : null;
        $product    = $request->filled('product') ? trim($request->product) : null;
        $line       = $request->filled('line') ? trim($request->line) : null;

        $q = Sop::query()
            ->whereBetween('created_at', [$from, $to])
            ->orderByDesc('created_at');

        if ($department) $q->where('department','like',"%{$department}%");
        if ($product)    $q->where('product','like',"%{$product}%");
        if ($line)       $q->where('line','like',"%{$line}%");

        $sops = $q->get();

        // === PDF jika package ada ===
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.sop_export_pdf', [
                'sops' => $sops,
                'from' => $from,
                'to'   => $to,
                'department' => $department,
                'product' => $product,
                'line' => $line,
            ])->setPaper('a4','portrait');

            $filename = "sop_export_{$from->format('Ymd')}_{$to->format('Ymd')}.pdf";
            return $pdf->download($filename);
        }

        // === Fallback CSV jika dompdf belum diinstall ===
        $filename = "sop_export_{$from->format('Ymd')}_{$to->format('Ymd')}.csv";
        $headers = [
            "Content-Type"        => "text/csv",
            "Content-Disposition" => "attachment; filename={$filename}",
        ];

        $columns = [
            'Created At',
            'Kode SOP',
            'Judul SOP',
            'Department',
            'Product',
            'Line',
            'Version',
            'Status',
            'Public',
        ];

        $callback = function () use ($sops, $columns) {
            $fh = fopen('php://output', 'w');
            fputcsv($fh, $columns);

            foreach ($sops as $s) {
                fputcsv($fh, [
                    optional($s->created_at)->format('Y-m-d H:i:s'),
                    $s->code ?? '-',
                    $s->title ?? '-',
                    $s->department ?? '-',
                    $s->product ?? '-',
                    $s->line ?? '-',
                    $s->version ?? '-',
                    $s->status ?? '-',
                    Schema::hasColumn('sops','is_public') ? ((int)$s->is_public ? 'YES':'NO') : '-',
                ]);
            }

            fclose($fh);
        };

        return response()->stream($callback, 200, $headers);
    }

    // =========================
    // HELPER
    // =========================
    private function safeParseDate($value, $fallback)
    {
        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return Carbon::parse($fallback);
        }
    }
}
