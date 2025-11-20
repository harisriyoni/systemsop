<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SopController;
use App\Http\Controllers\CheckSheetController;
use App\Http\Controllers\QrCenterController;
use Illuminate\Support\Facades\Route;

// AUTH
Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login')
    ->middleware('guest');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.post')
    ->middleware('guest');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// APP
Route::middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // SOP
    Route::prefix('sop')->name('sop.')->group(function () {
        Route::get('/', [SopController::class, 'index'])->name('index');

        Route::get('/create', [SopController::class, 'create'])
            ->name('create')
            ->middleware('role:admin,produksi');

        Route::post('/', [SopController::class, 'store'])
            ->name('store')
            ->middleware('role:admin,produksi');

        Route::get('/approval', [SopController::class, 'approvalList'])
            ->name('approval.index')
            ->middleware('role:admin,produksi,qa,logistik');

        Route::post('/{sop}/approve', [SopController::class, 'approve'])
            ->name('approve')
            ->middleware('role:admin,produksi,qa,logistik');

        Route::get('/{sop}', [SopController::class, 'show'])
            ->name('show');
    });

    // CHECK SHEET
    Route::prefix('check-sheets')->name('check_sheets.')->group(function () {
        Route::get('/', [CheckSheetController::class, 'index'])->name('index');

        Route::get('/create', [CheckSheetController::class, 'create'])
            ->name('create')
            ->middleware('role:admin,produksi,qa,logistik');

        Route::post('/', [CheckSheetController::class, 'store'])
            ->name('store')
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
            ->name('submissions');

        // Approval QA / Logistik
        Route::post('/submissions/{submission}/status', [CheckSheetController::class, 'updateStatus'])
            ->name('submissions.status')
            ->middleware('role:admin,qa,logistik');
    });

    // QR CENTER
    Route::get('/qr-center', [QrCenterController::class, 'index'])->name('qr_center.index');
});
