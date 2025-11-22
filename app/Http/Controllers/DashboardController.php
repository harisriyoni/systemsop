<?php

namespace App\Http\Controllers;

use App\Models\CheckSheet;
use App\Models\CheckSheetSubmission;
use App\Models\Sop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // =========================
        // FILTER INPUT
        // =========================
        $date       = $request->input('date');        // YYYY-MM-DD
        $department = $request->input('department');
        $product    = $request->input('product');
        $line       = $request->input('line');

        // normalize date
        $pickedDate = null;
        if ($date) {
            try {
                $pickedDate = Carbon::parse($date)->toDateString();
            } catch (\Throwable $e) {
                $pickedDate = null;
            }
        }
        $today = now()->toDateString();
        $dateForCs = $pickedDate ?: $today;

        // =========================
        // BASE QUERY (WITH FILTERS)
        // =========================
        $sopBase = Sop::query();
        $csBase  = CheckSheetSubmission::query();

        // Filter Department (partial match biar fleksibel)
        if ($department) {
            $dept = trim($department);
            $sopBase->where('department', 'like', "%{$dept}%");
            $csBase->whereHas('checkSheet', function ($q) use ($dept) {
                $q->where('department', 'like', "%{$dept}%");
            });
        }

        // Filter Product
        if ($product) {
            $prod = trim($product);
            $sopBase->where('product', 'like', "%{$prod}%");
            $csBase->whereHas('checkSheet', function ($q) use ($prod) {
                $q->where('product', 'like', "%{$prod}%");
            });
        }

        // Filter Line
        if ($line) {
            $ln = trim($line);
            $sopBase->where('line', 'like', "%{$ln}%");
            $csBase->whereHas('checkSheet', function ($q) use ($ln) {
                $q->where('line', 'like', "%{$ln}%");
            });
        }

        // =========================
        // SOP CARDS
        // =========================
        $sopTotal    = (clone $sopBase)->count();
        $sopDraft    = (clone $sopBase)->where('status', 'draft')->count();
        $sopWaiting  = (clone $sopBase)->where('status', 'waiting_approval')->count();
        $sopApproved = (clone $sopBase)->where('status', 'approved')->count();
        $sopExpired  = (clone $sopBase)->where('status', 'expired')->count();

        // =========================
        // CHECK SHEET TODAY CARDS
        // =========================
        $csTodayBase = (clone $csBase)->whereDate('submitted_at', $dateForCs);

        $csSubmitted   = (clone $csTodayBase)->where('status','submitted')->count();
        $csUnderReview = (clone $csTodayBase)->where('status','under_review')->count();
        $csApproved    = (clone $csTodayBase)->where('status','approved')->count();
        $csRejected    = (clone $csTodayBase)->where('status','rejected')->count();

        // =========================
        // NEED ACTION (MULTI-STAGE)
        // =========================
        // SOP pending per tahap (pakai flag kalau kolom ada)
        $sopPendingProduksi = 0;
        $sopPendingQa       = 0;
        $sopPendingLogistik = 0;

        $hasProdCol = Schema::hasColumn('sops', 'is_approved_produksi');
        $hasQaCol   = Schema::hasColumn('sops', 'is_approved_qa');
        $hasLogCol  = Schema::hasColumn('sops', 'is_approved_logistik');

        if ($hasProdCol && $hasQaCol && $hasLogCol) {
            $sopPendingProduksi = (clone $sopBase)
                ->where('status','waiting_approval')
                ->where('is_approved_produksi', false)
                ->count();

            $sopPendingQa = (clone $sopBase)
                ->where('status','waiting_approval')
                ->where('is_approved_produksi', true)
                ->where('is_approved_qa', false)
                ->count();

            $sopPendingLogistik = (clone $sopBase)
                ->where('status','waiting_approval')
                ->where('is_approved_produksi', true)
                ->where('is_approved_qa', true)
                ->where('is_approved_logistik', false)
                ->count();
        } else {
            // fallback kalau belum punya kolom stage
            $sopPendingQa = (clone $sopBase)
                ->where('status','waiting_approval')
                ->count();
        }

        // CS pending per tahap
        // NOTE: kalau kamu belum punya flag stage, ini fallback basic.
        $csPendingQa = (clone $csBase)
            ->whereIn('status', ['submitted','under_review'])
            ->count();

        $csPendingLogistik = 0;
        $hasFinalCol = Schema::hasColumn('check_sheet_submissions','is_final_approved_logistik');

        if ($hasFinalCol) {
            $csPendingLogistik = (clone $csBase)
                ->where('status', 'approved') // asumsi approved=qa approved
                ->where('is_final_approved_logistik', false)
                ->count();
        } else {
            // fallback lama kamu
            $csPendingLogistik = (clone $csBase)
                ->whereIn('status', ['submitted','under_review'])
                ->count();
        }

        // =========================
        // FILTER OPTIONS FOR UI (dropdown)
        // =========================
        $departments = Sop::select('department')->whereNotNull('department')->distinct()->orderBy('department')->pluck('department');
        $products    = Sop::select('product')->whereNotNull('product')->distinct()->orderBy('product')->pluck('product');
        $lines       = Sop::select('line')->whereNotNull('line')->distinct()->orderBy('line')->pluck('line');

        // =========================
        // OPTIONAL: TREND 7 HARI (buat chart pitching)
        // =========================
        $days = collect(range(0,6))->map(fn($i) => now()->subDays(6-$i)->toDateString());

        $sopTrendApproved = $days->map(function($d) use ($sopBase){
            return (clone $sopBase)
                ->where('status','approved')
                ->whereDate('updated_at',$d)
                ->count();
        });

        $csTrendSubmitted = $days->map(function($d) use ($csBase){
            return (clone $csBase)
                ->whereDate('submitted_at',$d)
                ->count();
        });

        // =========================
        // RETURN VIEW
        // =========================
        return view('dashboard', [
            'filters' => [
                'date'       => $pickedDate,
                'department' => $department,
                'product'    => $product,
                'line'       => $line,
            ],

            'filterOptions' => [
                'departments' => $departments,
                'products'    => $products,
                'lines'       => $lines,
            ],

            'sop' => [
                'total'    => $sopTotal,
                'draft'    => $sopDraft,
                'waiting'  => $sopWaiting,
                'approved' => $sopApproved,
                'expired'  => $sopExpired,
            ],

            'checkSheetToday' => [
                'submitted'   => $csSubmitted,
                'underReview' => $csUnderReview,
                'approved'    => $csApproved,
                'rejected'    => $csRejected,
                'date'        => $dateForCs,
            ],

            'needAction' => [
                'sop_pending_produksi' => $sopPendingProduksi,
                'sop_pending_qa'       => $sopPendingQa,
                'sop_pending_logistik' => $sopPendingLogistik,
                'cs_pending_qa'        => $csPendingQa,
                'cs_pending_logistik'  => $csPendingLogistik,
            ],

            'trend7d' => [
                'days'               => $days,
                'sopApproved'        => $sopTrendApproved,
                'checkSheetSubmitted'=> $csTrendSubmitted,
            ],
        ]);
    }
}
