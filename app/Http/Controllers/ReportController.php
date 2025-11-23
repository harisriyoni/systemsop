<?php

namespace App\Http\Controllers;

use App\Models\Sop;
use App\Models\CheckSheet;
use App\Models\CheckSheetSubmission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    // =========================
    // DASHBOARD REPORT (ALL TIME, NO DATE FILTER)
    // =========================
    public function index(Request $request)
    {
        $this->authorizeReport($request);

        // ===== VALIDASI INPUT (tanpa date) =====
        $validated = $request->validate([
            'department' => ['nullable','string','max:80'],
            'product'    => ['nullable','string','max:80'],
            'line'       => ['nullable','string','max:80'],
            'status'     => ['nullable','string', Rule::in(['submitted','under_review','approved','rejected'])],
            'type'       => ['nullable','string', Rule::in(['sop','checksheet'])], // buat dropdown blade, optional
        ]);

        $department = $validated['department'] ?? null;
        $product    = $validated['product'] ?? null;
        $line       = $validated['line'] ?? null;
        $subStatus  = $validated['status'] ?? null;
        $type       = $validated['type'] ?? null; // kalo mau filter tipe

        $deptLike = $this->escapeLike($department);
        $prodLike = $this->escapeLike($product);
        $lineLike = $this->escapeLike($line);

        // =========================================================
        // SOP REPORT (ALL TIME)
        // =========================================================
        $sopBase = Sop::query()
            // ->latestPerCode() // kalau mau hanya versi terbaru per code, buka ini
            ->when($department, fn($q) => $q->where('department', 'like', "%{$deptLike}%"))
            ->when($product,    fn($q) => $q->where('product',    'like', "%{$prodLike}%"))
            ->when($line,       fn($q) => $q->where('line',       'like', "%{$lineLike}%"));

        // kalau user pilih type=checksheet, SOP summary tetap dihitung? 
        // kalau mau SOP kosong saat type=checksheet:
        if ($type === 'checksheet') {
            $sopBase->whereRaw('1=0');
        }

        $sopTotal = (clone $sopBase)->count();

        $sopByStatus = (clone $sopBase)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total','status')
            ->toArray();

        $sopStatuses = ['draft','waiting_approval','approved','expired','rejected','archived'];
        foreach ($sopStatuses as $st) {
            $sopByStatus[$st] = $sopByStatus[$st] ?? 0;
        }

        $sopPublicStats = (object)[
            'total_public'  => 0,
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

        $sopPendingApproval = (object)[
            'total_waiting'     => 0,
            'produksi_pending'  => 0,
            'qa_pending'        => 0,
            'logistik_pending'  => 0,
        ];

        $hasProdCol = Schema::hasColumn('sops','is_approved_produksi');
        $hasQaCol   = Schema::hasColumn('sops','is_approved_qa');
        $hasLogCol  = Schema::hasColumn('sops','is_approved_logistik');

        if ($hasProdCol && $hasQaCol && $hasLogCol) {
            $sopPendingApproval = (clone $sopBase)
                ->where('status','waiting_approval')
                ->selectRaw("
                    COUNT(*) as total_waiting,
                    SUM(CASE WHEN is_approved_produksi = 0 THEN 1 ELSE 0 END) as produksi_pending,
                    SUM(CASE WHEN is_approved_qa = 0 THEN 1 ELSE 0 END) as qa_pending,
                    SUM(CASE WHEN is_approved_logistik = 0 THEN 1 ELSE 0 END) as logistik_pending
                ")
                ->first();
        } else {
            $sopPendingApproval->total_waiting =
                (clone $sopBase)->where('status','waiting_approval')->count();
        }

        // trend SOP per hari (last 30 days biar ringan)
        $sopPerDay = (clone $sopBase)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw("DATE(created_at) as day, COUNT(*) as total")
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // =========================================================
        // CHECK SHEET FORMS REPORT (ALL TIME)
        // =========================================================
        $formBase = CheckSheet::query()
            ->when($department, fn($q) => $q->where('department', 'like', "%{$deptLike}%"))
            ->when($product,    fn($q) => $q->where('product',    'like', "%{$prodLike}%"))
            ->when($line,       fn($q) => $q->where('line',       'like', "%{$lineLike}%"));

        if ($type === 'sop') {
            $formBase->whereRaw('1=0');
        }

        $formTotal = (clone $formBase)->count();

        $formByStatus = (clone $formBase)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total','status')
            ->toArray();

        $formStatuses = ['draft','active','inactive','archived'];
        foreach ($formStatuses as $st) {
            $formByStatus[$st] = $formByStatus[$st] ?? 0;
        }

        $formByDept = (clone $formBase)
            ->select('department', DB::raw('COUNT(*) as total'))
            ->groupBy('department')
            ->orderByDesc('total')
            ->pluck('total','department')
            ->toArray();

        // =========================================================
        // SUBMISSIONS REPORT (ALL TIME)
        // =========================================================
        $subBase = CheckSheetSubmission::query()
            ->whereNotNull('submitted_at')
            ->when($subStatus, fn($q) => $q->where('status', $subStatus))
            ->when(($department || $product || $line), function ($q) use ($deptLike,$prodLike,$lineLike,$department,$product,$line) {
                $q->whereHas('checkSheet', function ($cs) use ($deptLike,$prodLike,$lineLike,$department,$product,$line) {
                    if ($department) $cs->where('department','like',"%{$deptLike}%");
                    if ($product)    $cs->where('product','like',"%{$prodLike}%");
                    if ($line)       $cs->where('line','like',"%{$lineLike}%");
                });
            });

        if ($type === 'sop') {
            $subBase->whereRaw('1=0');
        }

        $subTotal = (clone $subBase)->count();

        $subByStatus = (clone $subBase)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total','status')
            ->toArray();

        $subStatuses = ['submitted','under_review','approved','rejected'];
        foreach ($subStatuses as $st) {
            $subByStatus[$st] = $subByStatus[$st] ?? 0;
        }

        // trend submissions per hari (last 30 days)
        $subPerDay = (clone $subBase)
            ->where('submitted_at', '>=', now()->subDays(30))
            ->selectRaw("DATE(submitted_at) as day, COUNT(*) as total")
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // TOP FORMS (ALL TIME sesuai filter)
        $topForms = CheckSheet::query()
            ->when($department, fn($q) => $q->where('department', 'like', "%{$deptLike}%"))
            ->when($product,    fn($q) => $q->where('product',    'like', "%{$prodLike}%"))
            ->when($line,       fn($q) => $q->where('line',       'like', "%{$lineLike}%"))
            ->withCount(['submissions as total' => function ($q) use ($subStatus) {
                $q->whereNotNull('submitted_at')
                  ->when($subStatus, fn($qq) => $qq->where('status', $subStatus));
            }])
            ->orderByDesc('total')
            ->limit(5)
            ->get(['id','title','department'])
            ->map(fn($cs) => [
                'id'    => $cs->id,
                'title' => $cs->title,
                'dept'  => $cs->department ?? '-',
                'total' => (int) $cs->total,
            ]);

        // TOP OPERATORS (ALL TIME sesuai filter)
        $topOperators = User::query()
            ->withCount(['submissions as total' => function ($q) use ($subStatus,$deptLike,$prodLike,$lineLike,$department,$product,$line) {
                $q->whereNotNull('submitted_at')
                  ->when($subStatus, fn($qq) => $qq->where('status', $subStatus))
                  ->when(($department || $product || $line), function ($qq) use ($deptLike,$prodLike,$lineLike,$department,$product,$line) {
                      $qq->whereHas('checkSheet', function ($cs) use ($deptLike,$prodLike,$lineLike,$department,$product,$line) {
                          if ($department) $cs->where('department','like',"%{$deptLike}%");
                          if ($product)    $cs->where('product','like',"%{$prodLike}%");
                          if ($line)       $cs->where('line','like',"%{$lineLike}%");
                      });
                  });
            }])
            ->whereHas('submissions', fn($q) => $q->whereNotNull('submitted_at'))
            ->orderByDesc('total')
            ->limit(5)
            ->get(['id','name'])
            ->map(fn($u) => [
                'id'    => $u->id,
                'name'  => $u->name ?? 'Unknown',
                'total' => (int) $u->total,
            ]);

        // =========================================================
        // ADAPTER VARS BUAT BLADE reports.index (BIAR GAK KOSONG)
        // =========================================================
        $sopTotals = [
            'draft'            => (int)($sopByStatus['draft'] ?? 0),
            'waiting_approval' => (int)($sopByStatus['waiting_approval'] ?? 0),
            'approved'         => (int)($sopByStatus['approved'] ?? 0),
            'expired'          => (int)($sopByStatus['expired'] ?? 0),
        ];

        $formTotals = [
            'active'   => (int)($formByStatus['active'] ?? 0),
            'draft'    => (int)($formByStatus['draft'] ?? 0),
            'inactive' => (int)($formByStatus['inactive'] ?? 0),
        ];

        $subTotals = [
            'submitted'    => (int)($subByStatus['submitted'] ?? 0),
            'under_review' => (int)($subByStatus['under_review'] ?? 0),
            'approved'     => (int)($subByStatus['approved'] ?? 0),
            'rejected'     => (int)($subByStatus['rejected'] ?? 0),
        ];

        // Recent SOP (last 5)
        $recentSops = (clone $sopBase)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id','code','title','department','status','created_at']);

        // Recent Submissions (last 5)
        $recentSubs = (clone $subBase)
            ->with([
                'checkSheet:id,title,department',
                'operator:id,name',
            ])
            ->orderByDesc('submitted_at')
            ->limit(5)
            ->get();

        return view('reports.index', [
            // filters
            'department' => $department,
            'product'    => $product,
            'line'       => $line,
            'status'     => $subStatus,
            'type'       => $type,

            // ✅ sesuai blade
            'sopTotals'  => $sopTotals,
            'formTotals' => $formTotals,
            'subTotals'  => $subTotals,
            'recentSops' => $recentSops,
            'recentSubs' => $recentSubs,

            // optional kalau nanti dipakai chart lain
            'sopTotal'           => $sopTotal,
            'sopByStatus'        => $sopByStatus,
            'sopPublicStats'     => $sopPublicStats,
            'sopPendingApproval' => $sopPendingApproval,
            'sopPerDay'          => $sopPerDay,

            'formTotal'    => $formTotal,
            'formByStatus' => $formByStatus,
            'formByDept'   => $formByDept,

            'subTotal'     => $subTotal,
            'subByStatus'  => $subByStatus,
            'subPerDay'    => $subPerDay,
            'topForms'     => $topForms,
            'topOperators' => $topOperators,
        ]);
    }

    // =========================
    // EXPORT SUBMISSIONS CSV (ALL TIME)
    // =========================
    public function exportSubmissionsCsv(Request $request)
    {
        $this->authorizeReport($request);

        $validated = $request->validate([
            'department' => ['nullable','string','max:80'],
            'product'    => ['nullable','string','max:80'],
            'line'       => ['nullable','string','max:80'],
            'status'     => ['nullable','string', Rule::in(['submitted','under_review','approved','rejected'])],
        ]);

        $department = $validated['department'] ?? null;
        $product    = $validated['product'] ?? null;
        $line       = $validated['line'] ?? null;
        $status     = $validated['status'] ?? null;

        $deptLike = $this->escapeLike($department);
        $prodLike = $this->escapeLike($product);
        $lineLike = $this->escapeLike($line);

        $q = CheckSheetSubmission::query()
            ->with(['checkSheet:id,title,department,product,line','operator:id,name','reviewer:id,name'])
            ->whereNotNull('submitted_at')
            ->when($status, fn($qq) => $qq->where('status', $status))
            ->when(($department || $product || $line), function ($qq) use ($department,$product,$line,$deptLike,$prodLike,$lineLike) {
                $qq->whereHas('checkSheet', function ($cs) use ($department,$product,$line,$deptLike,$prodLike,$lineLike) {
                    if ($department) $cs->where('department','like',"%{$deptLike}%");
                    if ($product)    $cs->where('product','like',"%{$prodLike}%");
                    if ($line)       $cs->where('line','like',"%{$lineLike}%");
                });
            })
            ->orderByDesc('submitted_at');

        $filename = "checksheet_submissions_alltime.csv";
        $headers = [
            "Content-Type"        => "text/csv",
            "Content-Disposition" => "attachment; filename={$filename}",
        ];

        $columns = [
            'Submitted At',
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

        $callback = function () use ($q, $columns) {
            $fh = fopen('php://output', 'w');
            fputcsv($fh, $columns);

            $q->chunk(500, function ($rows) use ($fh) {
                foreach ($rows as $r) {
                    $data = is_array($r->data) ? $r->data : (json_decode($r->data, true) ?: []);
                    fputcsv($fh, [
                        optional($r->submitted_at)->format('Y-m-d H:i:s'),
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
            });

            fclose($fh);
        };

        return response()->stream($callback, 200, $headers);
    }

    // =========================
    // EXPORT SOP PDF / CSV (ALL TIME)
    // =========================
    public function exportSopPdf(Request $request)
    {
        $this->authorizeReport($request);

        $validated = $request->validate([
            'department' => ['nullable','string','max:80'],
            'product'    => ['nullable','string','max:80'],
            'line'       => ['nullable','string','max:80'],
        ]);

        $department = $validated['department'] ?? null;
        $product    = $validated['product'] ?? null;
        $line       = $validated['line'] ?? null;

        $deptLike = $this->escapeLike($department);
        $prodLike = $this->escapeLike($product);
        $lineLike = $this->escapeLike($line);

        $q = Sop::query()
            // ->latestPerCode() // kalau mau latest only
            ->when($department, fn($qq) => $qq->where('department','like',"%{$deptLike}%"))
            ->when($product,    fn($qq) => $qq->where('product','like',"%{$prodLike}%"))
            ->when($line,       fn($qq) => $qq->where('line','like',"%{$lineLike}%"))
            ->orderByDesc('created_at');

        $sops = $q->get();

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.sop_export_pdf', [
                'sops'       => $sops,
                'department' => $department,
                'product'    => $product,
                'line'       => $line,
            ])->setPaper('a4','portrait');

            return $pdf->download("sop_export_alltime.pdf");
        }

        // fallback CSV
        $filename = "sop_export_alltime.csv";
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
                    Schema::hasColumn('sops','is_public')
                        ? ((int)$s->is_public ? 'YES' : 'NO')
                        : '-',
                ]);
            }

            fclose($fh);
        };

        return response()->stream($callback, 200, $headers);
    }

    // =========================
    // SECURITY / HELPERS
    // =========================
    private function authorizeReport(Request $request): void
    {
        $u = $request->user();
        abort_unless($u && $u->isRole(['admin','produksi','qa','logistik']), 403);
    }

    private function escapeLike(?string $value): ?string
    {
        if ($value === null) return null;
        $v = trim($value);
        if ($v === '') return null;
        return addcslashes($v, '%_\\');
    }
}
