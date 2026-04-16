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
	Route::get('/lancamentos/exportar-pdf', [LancamentoController::class, 'exportPdf'])->name('lancamentos.export-pdf');
	Route::get('/lancamentos/criar', [LancamentoController::class, 'create'])->name('lancamentos.create');
	Route::post('/lancamentos', [LancamentoController::class, 'store'])->name('lancamentos.store');
	Route::get('/lancamentos/{lancamento}/editar', [LancamentoController::class, 'edit'])->name('lancamentos.edit');
	Route::put('/lancamentos/{lancamento}', [LancamentoController::class, 'update'])->name('lancamentos.update');
	Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
