<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\PasswordController;
use Illuminate\Support\Facades\Route;

/*
 * Pendaftaran mandiri sengaja dinonaktifkan — akun guru dibuat oleh admin
 * melalui menu Pengguna & Peran.
 */

Route::middleware('guest')->group(function () {
    Route::get('masuk', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('masuk', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('konfirmasi-sandi', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');
    Route::post('konfirmasi-sandi', [ConfirmablePasswordController::class, 'store']);

    Route::put('sandi', [PasswordController::class, 'update'])->name('password.update');

    Route::post('keluar', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
