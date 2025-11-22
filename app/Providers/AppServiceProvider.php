<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

// ✅ sesuaikan model sesuai project kamu
use App\Models\Sop;
use App\Models\CheckSheetSubmission;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // kosongin aja kalau belum ada kebutuhan
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
         * Global View Composer
         * - ngisi $needAction biar sidebar badge jalan di semua halaman
         */
        View::composer('*', function ($view) {

            // kalau belum login, jangan hitung apa-apa
            if (!auth()->check()) {
                return;
            }

            $user = auth()->user();

            // default aman (biar gak undefined index)
            $needAction = [
                // SOP
                'sop_pending_produksi' => 0,
                'sop_pending_qa'       => 0,
                'sop_pending_logistik'=> 0,

                // Check Sheet (pending approval)
                'cs_pending'          => 0,
            ];

            // =========================
            // SOP Pending by Role
            // =========================
            if ($user->role === 'produksi') {
                $needAction['sop_pending_produksi'] = Sop::where('status', 'waiting_approval')
                    ->where('is_approved_produksi', false)
                    ->count();
            }

            if ($user->role === 'qa') {
                $needAction['sop_pending_qa'] = Sop::where('status', 'waiting_approval')
                    ->where('is_approved_qa', false)
                    ->count();
            }

            if ($user->role === 'logistik') {
                $needAction['sop_pending_logistik'] = Sop::where('status', 'waiting_approval')
                    ->where('is_approved_logistik', false)
                    ->count();
            }

            // admin bisa lihat total pending SOP (opsional)
            if ($user->role === 'admin') {
                $needAction['sop_pending_produksi'] = Sop::where('status', 'waiting_approval')
                    ->where('is_approved_produksi', false)
                    ->count();
                $needAction['sop_pending_qa'] = Sop::where('status', 'waiting_approval')
                    ->where('is_approved_qa', false)
                    ->count();
                $needAction['sop_pending_logistik'] = Sop::where('status', 'waiting_approval')
                    ->where('is_approved_logistik', false)
                    ->count();
            }

            // =========================
            // Check Sheet Pending Approval
            // =========================
            if (in_array($user->role, ['admin','qa','logistik'])) {
                $needAction['cs_pending'] = CheckSheetSubmission::where('status', 'submitted')->count();
            }

            // inject ke semua view
            $view->with('needAction', $needAction);
        });
    }
}
