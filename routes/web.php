<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SopController;
use App\Http\Controllers\CheckSheetController;
use App\Http\Controllers\QrCenterController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;

// =========================
// AUTH
// =========================
Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login')
    ->middleware('guest');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.post')
    ->middleware('guest');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');


// =========================
// SOP PUBLIC (QR TANPA LOGIN)
// =========================
// NOTE:
// - publicShow()    => tampil SOP publik
// - publicUnlock()  => cek PIN lalu set session unlock
// - publicAck()     => operator klik "Saya sudah baca" (opsional)
Route::prefix('public/sop')->name('sop.public.')->group(function () {
    Route::get('/{sop}', [SopController::class, 'publicShow'])->name('show');
    Route::post('/{sop}/unlock', [SopController::class, 'publicUnlock'])->name('unlock');
    Route::post('/{sop}/ack', [SopController::class, 'publicAck'])->name('ack'); // opsional
});


// =========================
// APP (AUTH AREA)
// =========================
Route::middleware('auth')->group(function () {

    // DASHBOARD
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');


    // =========================
    // SOP
    // =========================
    Route::prefix('sop')->name('sop.')->group(function () {

        // list SOP
        Route::get('/', [SopController::class, 'index'])->name('index');

        // create SOP
        Route::get('/create', [SopController::class, 'create'])
            ->name('create')
            ->middleware('role:admin,produksi');

        Route::post('/', [SopController::class, 'store'])
            ->name('store')
            ->middleware('role:admin,produksi');

        // edit / revise SOP
        Route::get('/{sop}/edit', [SopController::class, 'edit'])
            ->name('edit')
            ->middleware('role:admin,produksi');

        Route::patch('/{sop}', [SopController::class, 'update'])
            ->name('update')
            ->middleware('role:admin,produksi');

        // delete SOP (optional)
        Route::delete('/{sop}', [SopController::class, 'destroy'])
            ->name('destroy')
            ->middleware('role:admin');

        // submit approval (draft -> waiting approval)
        Route::post('/{sop}/submit', [SopController::class, 'submitApproval'])
            ->name('submit')
            ->middleware('role:admin,produksi');

        // approval list (approver view)
        Route::get('/approval', [SopController::class, 'approvalList'])
            ->name('approval.index')
            ->middleware('role:admin,produksi,qa,logistik');

        // approve / reject SOP
        Route::post('/{sop}/approve', [SopController::class, 'approve'])
            ->name('approve')
            ->middleware('role:admin,produksi,qa,logistik');

        Route::post('/{sop}/reject', [SopController::class, 'reject'])
            ->name('reject')
            ->middleware('role:admin,produksi,qa,logistik');

        // generate QR SOP
        Route::post('/{sop}/qr', [SopController::class, 'generateQr'])
            ->name('qr')
            ->middleware('role:admin,produksi,qa,logistik');

        // download PDF SOP
        Route::get('/{sop}/download', [SopController::class, 'downloadPdf'])
            ->name('download')
            ->middleware('role:admin,produksi,qa,logistik');

        // versions/history/audit trail (opsional)
        Route::get('/{sop}/versions', [SopController::class, 'versions'])
            ->name('versions')
            ->middleware('role:admin,produksi,qa,logistik');

        Route::get('/{sop}/history', [SopController::class, 'history'])
            ->name('history')
            ->middleware('role:admin,produksi,qa,logistik');

        // show SOP detail (internal)
        Route::get('/{sop}', [SopController::class, 'show'])
            ->name('show');
    });


    // =========================
    // CHECK SHEET
    // =========================
    Route::prefix('check-sheets')->name('check_sheets.')->group(function () {

        // list form
        Route::get('/', [CheckSheetController::class, 'index'])->name('index');

        // create form
        Route::get('/create', [CheckSheetController::class, 'create'])
            ->name('create')
            ->middleware('role:admin,produksi,qa,logistik');

        Route::post('/', [CheckSheetController::class, 'store'])
            ->name('store')
            ->middleware('role:admin,produksi,qa,logistik');

        // edit form (revise)
        Route::get('/{checkSheet}/edit', [CheckSheetController::class, 'edit'])
            ->name('edit')
            ->middleware('role:admin,produksi,qa,logistik');

        Route::patch('/{checkSheet}', [CheckSheetController::class, 'update'])
            ->name('update')
            ->middleware('role:admin,produksi,qa,logistik');

        // delete form (optional)
        Route::delete('/{checkSheet}', [CheckSheetController::class, 'destroy'])
            ->name('destroy')
            ->middleware('role:admin');

        // publish / unpublish form
        Route::post('/{checkSheet}/publish', [CheckSheetController::class, 'publish'])
            ->name('publish')
            ->middleware('role:admin,produksi,qa,logistik');

        Route::post('/{checkSheet}/unpublish', [CheckSheetController::class, 'unpublish'])
            ->name('unpublish')
            ->middleware('role:admin,produksi,qa,logistik');

        // generate QR form
        Route::post('/{checkSheet}/qr', [CheckSheetController::class, 'generateQr'])
            ->name('qr')
            ->middleware('role:admin,produksi,qa,logistik');

        // Operator isi via QR
        Route::get('/{checkSheet}/fill', [CheckSheetController::class, 'fill'])
            ->name('fill')
            ->middleware('role:admin,operator,produksi,qa,logistik');

        Route::post('/{checkSheet}/fill', [CheckSheetController::class, 'submit'])
            ->name('submit')
            ->middleware('role:admin,operator,produksi,qa,logistik');

        // List submissions
        Route::get('/submissions', [CheckSheetController::class, 'submissions'])
            ->name('submissions')
            ->middleware('role:admin,produksi,qa,logistik');

        // Detail submission (optional)
        Route::get('/submissions/{submission}', [CheckSheetController::class, 'showSubmission'])
            ->name('submissions.show')
            ->middleware('role:admin,produksi,qa,logistik');

        // Approval QA / Logistik
        Route::match(['post', 'patch'], '/submissions/{submission}/status', [CheckSheetController::class, 'updateStatus'])
            ->name('submissions.status')
            ->middleware('role:admin,qa,logistik');
    });


    // =========================
    // QR CENTER
    // =========================
    Route::get('/qr-center', [QrCenterController::class, 'index'])
        ->name('qr_center.index')
        ->middleware('role:admin,produksi,qa,logistik');


    // =========================
    // REPORTS / EXPORT
    // =========================
    Route::prefix('reports')->name('reports.')->middleware('role:admin,produksi,qa,logistik')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');

        Route::get('/submissions/export', [ReportController::class, 'exportSubmissionsCsv'])
            ->name('submissions.export');

        Route::get('/sop/export', [ReportController::class, 'exportSopPdf'])
            ->name('sop.export');
    });


    // =========================
    // AKSES USER (ADMIN ONLY)
    // =========================
    Route::prefix('users')->name('users.')->middleware('role:admin')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');

        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');

        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::patch('/{user}', [UserController::class, 'update'])->name('update');

        // === tambahan dari controller full ===
        Route::post('/{user}/reset-password', [UserController::class, 'resetPassword'])
            ->name('reset_password');

        Route::post('/{user}/toggle-active', [UserController::class, 'toggleActive'])
            ->name('toggle_active');
        // ================================

        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

});
