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
// - publicShow() => tampil SOP publik
// - publicUnlock() => cek PIN lalu set session unlock
Route::prefix('public/sop')->name('sop.public.')->group(function () {
    Route::get('/{sop}', [SopController::class, 'publicShow'])->name('show');
    Route::post('/{sop}/unlock', [SopController::class, 'publicUnlock'])->name('unlock');
});


// =========================
// APP (AUTH AREA)
// =========================
Route::middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index')
        ->middleware('role:admin,produksi,qa,logistik');

    Route::get('/reports/submissions/export', [ReportController::class, 'exportSubmissionsCsv'])
        ->name('reports.submissions.export')
        ->middleware('role:admin,qa,logistik');
    // =========================
    // SOP
    // =========================
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
        // FIX: match post + patch biar form boleh pakai @method('PATCH')
        Route::match(['post', 'patch'], '/submissions/{submission}/status', [CheckSheetController::class, 'updateStatus'])
            ->name('submissions.status')
            ->middleware('role:admin,qa,logistik');
    });


    // =========================
    // QR CENTER
    // =========================
    Route::get('/qr-center', [QrCenterController::class, 'index'])
        ->name('qr_center.index');


    // =========================
    // REPORTS
    // =========================
    // menu: route('reports.index')
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])
            ->name('index')
            ->middleware('role:admin,produksi,qa,logistik');
    });


    // =========================
    // AKSES USER (ADMIN ONLY)
    // =========================
    // menu: route('users.index')
    Route::prefix('users')->name('users.')->middleware('role:admin')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        // optional kalau mau CRUD:
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::patch('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });
});
