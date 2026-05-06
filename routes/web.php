<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\AccountController;
use App\Http\Controllers\Management\SchoolYearController;
use App\Http\Controllers\Management\BranchController;
use App\Http\Controllers\Sarf\ActivityController;
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

    });


Route::middleware(['auth', 'Branch_OSA'])->prefix('branch_osa')
    ->name('branch_osa.')
    ->group(function () {
        // Dashboard
        Route::get('/', [Branch_OSA_Controller::class, 'index'])->name('index');
    });

Route::middleware(['auth', 'Staff_OSA'])->prefix('staff_osa')
    ->name('staff_osa.')
    ->group(function () {
        // Dashboard
        Route::get('/', [Staff_OSA_Controller::class, 'index'])->name('index');
    });