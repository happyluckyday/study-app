<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\Auth\SessionController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

use App\Http\Controllers\Admin\ImpersonationController;

// Social Login
Route::get('auth/{provider}', [SocialLoginController::class, 'redirect'])->name('social.redirect');
Route::get('auth/{provider}/callback', [SocialLoginController::class, 'callback'])->name('social.callback');
Route::get('auth/social/bind', [SocialLoginController::class, 'showBindForm'])->name('social.bind');
Route::post('auth/social/bind', [SocialLoginController::class, 'bind'])->name('social.bind.submit');

// Admin Impersonation
Route::get('admin/impersonate/{id}', [ImpersonationController::class, 'impersonate'])->name('admin.impersonate');
Route::get('admin/impersonate-leave', [ImpersonationController::class, 'leave'])->name('admin.impersonate.leave');

// Garbage Collection for Session
Route::post('api/session/acknowledge-kick', [SessionController::class, 'acknowledgeKick'])->name('session.acknowledge_kick');
