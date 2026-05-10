<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\AccountController;
use App\Http\Controllers\Management\SchoolYearController;
use App\Http\Controllers\Management\BranchController;
use App\Http\Controllers\Management\SystemLogController;
use App\Http\Controllers\Sarf\ActivityController;
use App\Http\Controllers\Sarf\ApprovalController;
use App\Http\Controllers\Sarf\PaarController;
use App\Http\Controllers\Sarf\TracerController;
use App\Http\Controllers\Sarf\SarfDocumentController;
use App\Http\Controllers\Usertype\Dean_OSA_Controller;
use App\Http\Controllers\Usertype\Staff_OSA_Controller;
use App\Http\Controllers\Usertype\Branch_OSA_Controller;



Route::get('/', fn() => view('log.login'))->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'Dean_OSA'])->prefix('dean_osa')
    ->name('dean_osa.')
    ->group(function () {

        // Dashboard
        Route::get('/', [Dean_OSA_Controller::class, 'index'])->name('index');

        // Account Management
        Route::resource('account', AccountController::class);

        // School Year Management
        Route::resource('schoolyear', SchoolYearController::class);
        Route::patch('schoolyear/{id}/set-current', [SchoolYearController::class, 'setCurrent'])->name('schoolyear.set-current');

        // Branch Management
        Route::resource('branch', BranchController::class);

        // Activity Management
        Route::resource('activity', ActivityController::class);

        // Approval Management
        Route::get('approval', [ApprovalController::class, 'index'])->name('approval.index');
        Route::get('approval/{id}', [ApprovalController::class, 'show'])->name('approval.show');
        Route::get('approval/{id}/review', [ApprovalController::class, 'review'])->name('approval.review');
        Route::match(['post', 'patch'], 'approval/{id}/status', [ApprovalController::class, 'updateStatus'])->name('approval.status');
        Route::match(['post', 'patch'], 'approval/{id}/approve', [ApprovalController::class, 'approve'])->name('approval.approve');
        Route::post('approval/{id}/document', [ApprovalController::class, 'storeDocument'])->name('approval.document.store');

        // Rescheduling (separate from signatory approvals)
        Route::post('approval/{id}/reschedule', [ApprovalController::class, 'requestReschedule'])->name('approval.reschedule.request');
        Route::post('approval/{id}/reschedule/approve', [ApprovalController::class, 'approveReschedule'])->name('approval.reschedule.approve');
        Route::post('approval/{id}/reschedule/reject', [ApprovalController::class, 'rejectReschedule'])->name('approval.reschedule.reject');

        // PAAR (Post-Activity Accomplishment Report)
        Route::get('paar', [PaarController::class, 'index'])->name('paar.index');
        Route::get('paar/{id}', [PaarController::class, 'show'])->name('paar.show');
        Route::get('paar/{id}/edit', [PaarController::class, 'edit'])->name('paar.edit');
        Route::get('paar/{id}/act', [PaarController::class, 'act'])->name('paar.act');
        Route::post('paar/{id}', [PaarController::class, 'update'])->name('paar.update');

        // Tracer
        Route::get('tracer', [TracerController::class, 'index'])->name('tracer.index');
        Route::get('tracer/{id}', [TracerController::class, 'show'])->name('tracer.show');

        // System Logs
        Route::get('system-logs', [SystemLogController::class, 'index'])->name('system-logs.index');

        // SARF Documents (view/download)
        Route::get('sarf-documents/{document}', [SarfDocumentController::class, 'show'])->name('sarf-documents.show');

    });


Route::middleware(['auth', 'Staff_OSA'])->prefix('staff_osa')
    ->name('staff_osa.')
    ->group(function () {

        // Dashboard
        Route::get('/', [Staff_OSA_Controller::class, 'index'])->name('index');

        // Activities (read-only)
        Route::get('activity', [Staff_OSA_Controller::class, 'activityIndex'])->name('activity.index');
        Route::get('activity/{id}', [Staff_OSA_Controller::class, 'activityShow'])->name('activity.show');

        // Approvals
        Route::get('approval', [Staff_OSA_Controller::class, 'approvalIndex'])->name('approval.index');

        // PAAR (Post-Activity Accomplishment Report)
        Route::get('paar', [Staff_OSA_Controller::class, 'paarIndex'])->name('paar.index');

    });


Route::middleware(['auth', 'Branch_OSA'])->prefix('branch_osa')
    ->name('branch_osa.')
    ->group(function () {

        // Dashboard
        Route::get('/', [Branch_OSA_Controller::class, 'index'])->name('index');

        // Tracer (scoped to user's designated branch)
        Route::get('tracer', [Branch_OSA_Controller::class, 'tracerIndex'])->name('tracer.index');
        Route::get('tracer/{id}', [Branch_OSA_Controller::class, 'tracerShow'])->name('tracer.show');

    });
