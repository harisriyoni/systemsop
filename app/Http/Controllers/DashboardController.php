<?php

namespace App\Http\Controllers;

use App\Models\CheckSheet;
use App\Models\CheckSheetSubmission;
use App\Models\Sop;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date');
        $department = $request->input('department');
        $product = $request->input('product');
        $line = $request->input('line');

        // FILTER BASE
        $sopBase = Sop::query();
        $csBase = CheckSheetSubmission::query();

        if ($department) {
            $sopBase->where('department', $department);
            $csBase->whereHas('checkSheet', function ($q) use ($department) {
                $q->where('department', $department);
            });
        }

        if ($product) {
            $sopBase->where('product', $product);
            $csBase->whereHas('checkSheet', function ($q) use ($product) {
                $q->where('product', $product);
            });
        }

        if ($line) {
            $sopBase->where('line', $line);
            $csBase->whereHas('checkSheet', function ($q) use ($line) {
                $q->where('line', $line);
            });
        }

        // SOP CARDS
        $sopTotal   = (clone $sopBase)->count();
        $sopDraft   = (clone $sopBase)->where('status', 'draft')->count();
        $sopWaiting = (clone $sopBase)->where('status', 'waiting_approval')->count();
        $sopApproved= (clone $sopBase)->where('status', 'approved')->count();
        $sopExpired = (clone $sopBase)->where('status', 'expired')->count();

        // CHECK SHEET TODAY
        $csTodayBase = clone $csBase;
        if ($date) {
            $csTodayBase->whereDate('submitted_at', $date);
        } else {
            $csTodayBase->whereDate('submitted_at', now()->toDateString());
        }

        $csSubmitted   = (clone $csTodayBase)->where('status','submitted')->count();
        $csUnderReview = (clone $csTodayBase)->where('status','under_review')->count();
        $csApproved    = (clone $csTodayBase)->where('status','approved')->count();
        $csRejected    = (clone $csTodayBase)->where('status','rejected')->count();

        // NEED ACTION
        $sopPendingQa = (clone $sopBase)
            ->where('status','waiting_approval')
            ->where('is_approved_qa', false)
            ->count();

        $csPendingLogistik = (clone $csBase)
            ->whereIn('status', ['submitted','under_review'])
            ->count();

        return view('dashboard', [
            'filters' => [
                'date' => $date,
                'department' => $department,
                'product' => $product,
                'line' => $line,
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
            ],
            'needAction' => [
                'sop_pending_qa'       => $sopPendingQa,
                'cs_pending_logistik'  => $csPendingLogistik,
            ],
        ]);
    }
}
