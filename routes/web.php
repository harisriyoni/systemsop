<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SopController;
use App\Http\Controllers\CheckSheetController;
use App\Http\Controllers\QrCenterController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SopTemplateController;

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
Route::prefix('public/sop')->name('sop.public.')->group(function () {
    Route::get('/{sop}', [SopController::class, 'publicShow'])->name('show');
    Route::post('/{sop}/unlock', [SopController::class, 'publicUnlock'])->name('unlock');
    Route::post('/{sop}/ack', [SopController::class, 'publicAck'])->name('ack');
});


// =========================
// APP (AUTH AREA)
// =========================
Route::middleware('auth')->group(function () {

    // DASHBOARD
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // =========================
    // PROFILE (SELF SERVICE)
    // =========================
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');

        Route::patch('/', [ProfileController::class, 'update'])->name('update');

        Route::patch('/password', [ProfileController::class, 'updatePassword'])
            ->name('password.update');

        Route::post('/avatar', [ProfileController::class, 'updateAvatar'])
            ->name('avatar.update');

        Route::delete('/avatar', [ProfileController::class, 'deleteAvatar'])
            ->name('avatar.delete');
    });


    // =========================
    // SOP + SOP TEMPLATES
    // =========================
    Route::prefix('sop')->name('sop.')->group(function () {

        // =========================
        // SOP TEMPLATES (NESTED)
        // URL: /sop/templates/...
        // NAME: sop.templates.*
        // WAJIB di atas route /{sop}
        // =========================
        Route::prefix('templates')->name('templates.')->group(function () {

            Route::get('/', [SopTemplateController::class, 'index'])
                ->name('index')
                ->middleware('role:admin,produksi');

            Route::get('/create', [SopTemplateController::class, 'create'])
                ->name('create')
                ->middleware('role:admin,produksi');

            Route::post('/', [SopTemplateController::class, 'store'])
                ->name('store')
                ->middleware('role:admin,produksi');

            Route::get('/{template}/edit', [SopTemplateController::class, 'edit'])
                ->name('edit')
                ->middleware('role:admin,produksi');

            Route::match(['put', 'patch'], '/{template}', [SopTemplateController::class, 'update'])
                ->name('update')
                ->middleware('role:admin,produksi');

            Route::delete('/{template}', [SopTemplateController::class, 'destroy'])
                ->name('destroy')
                ->middleware('role:admin,produksi');

            // JSON TEMPLATE (BUAT LOAD TEMPLATE KE CREATE SOP)
            Route::get('/{template}/json', [SopTemplateController::class, 'showJson'])
                ->name('json')
                ->middleware('role:admin,produksi');

            // SHOW / VIEW PDF TEMPLATE
            Route::get('/{template}', [SopTemplateController::class, 'show'])
                ->name('show')
                ->middleware('role:admin,produksi');
        });

        // =========================
        // SOP JSON (BUAT IMPORT KE TEMPLATE)
        // URL: /sop/{sop}/json
        // NAME: sop.json
        // WAJIB di luar templates group
        // WAJIB sebelum semua wildcard /{sop} lainnya
        // =========================
        Route::get('/{sop}/json', [SopController::class, 'showJson'])
            ->name('json')
            ->middleware('role:admin,produksi,qa,logistik');

        // =========================
        // SOP ROUTES
        // =========================

        // list SOP
        Route::get('/', [SopController::class, 'index'])->name('index');

        // create SOP
        Route::get('/create', [SopController::class, 'create'])
            ->name('create')
            ->middleware('role:admin,produksi');

        Route::post('/', [SopController::class, 'store'])
            ->name('store')
            ->middleware('role:admin,produksi');

        // approval list (approver view)
        Route::get('/approval', [SopController::class, 'approvalList'])
            ->name('approval.index')
            ->middleware('role:admin,produksi,qa,logistik');

        // versions/history global
        Route::get('/versions', [SopController::class, 'versionsIndex'])
            ->name('versions.index')
            ->middleware('role:admin,produksi,qa,logistik');

        Route::get('/history', [SopController::class, 'historyIndex'])
            ->name('history.index')
            ->middleware('role:admin,produksi,qa,logistik');

        // edit / revise SOP
        Route::get('/{sop}/edit', [SopController::class, 'edit'])
            ->name('edit')
            ->middleware('role:admin,produksi');

        // update SOP (PUT & PATCH)
        Route::match(['put', 'patch'], '/{sop}', [SopController::class, 'update'])
            ->name('update')
            ->middleware('role:admin,produksi');

        // delete SOP
        Route::delete('/{sop}', [SopController::class, 'destroy'])
            ->name('destroy')
            ->middleware('role:admin');

        // submit approval (draft -> waiting approval)
        Route::post('/{sop}/submit', [SopController::class, 'submitApproval'])
            ->name('submit')
            ->middleware('role:admin,produksi');

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

        // versions/history per SOP (detail)
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

        Route::get('/', [CheckSheetController::class, 'index'])->name('index');

        Route::get('/create', [CheckSheetController::class, 'create'])
            ->name('create')
            ->middleware('role:admin,produksi,qa,logistik');

        Route::post('/', [CheckSheetController::class, 'store'])
            ->name('store')
            ->middleware('role:admin,produksi,qa,logistik');

        Route::get('/{checkSheet}/edit', [CheckSheetController::class, 'edit'])
            ->name('edit')
            ->middleware('role:admin,produksi,qa,logistik');

        Route::patch('/{checkSheet}', [CheckSheetController::class, 'update'])
            ->name('update')
            ->middleware('role:admin,produksi,qa,logistik');

        Route::delete('/{checkSheet}', [CheckSheetController::class, 'destroy'])
            ->name('destroy')
            ->middleware('role:admin');

        Route::post('/{checkSheet}/publish', [CheckSheetController::class, 'publish'])
            ->name('publish')
            ->middleware('role:admin,produksi,qa,logistik');

        Route::post('/{checkSheet}/unpublish', [CheckSheetController::class, 'unpublish'])
            ->name('unpublish')
            ->middleware('role:admin,produksi,qa,logistik');

        Route::post('/{checkSheet}/qr', [CheckSheetController::class, 'generateQr'])
            ->name('qr')
            ->middleware('role:admin,produksi,qa,logistik');

        Route::get('/{checkSheet}/fill', [CheckSheetController::class, 'fill'])
            ->name('fill')
            ->middleware('role:admin,operator,produksi,qa,logistik');

        Route::post('/{checkSheet}/fill', [CheckSheetController::class, 'submit'])
            ->name('submit')
            ->middleware('role:admin,operator,produksi,qa,logistik');

        Route::get('/submissions', [CheckSheetController::class, 'submissions'])
            ->name('submissions')
            ->middleware('role:admin,produksi,qa,logistik');

        Route::get('/submissions/{submission}', [CheckSheetController::class, 'showSubmission'])
            ->name('submissions.show')
            ->middleware('role:admin,produksi,qa,logistik');

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
    Route::prefix('reports')
        ->name('reports.')
        ->middleware('role:admin,produksi,qa,logistik')
        ->group(function () {

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

        Route::post('/{user}/reset-password', [UserController::class, 'resetPassword'])
            ->name('reset_password');

        Route::post('/{user}/toggle-active', [UserController::class, 'toggleActive'])
            ->name('toggle_active');

        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

});
