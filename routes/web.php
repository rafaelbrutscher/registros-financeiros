<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LancamentoController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
	return Auth::check()
		? redirect()->route('lancamentos.index')
		: redirect()->route('login');
});

Route::middleware('guest')->group(function () {
	Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
	Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
	Route::get('/lancamentos', [LancamentoController::class, 'index'])->name('lancamentos.index');
	Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
