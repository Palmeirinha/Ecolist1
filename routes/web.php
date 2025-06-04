<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AlimentoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReceitaController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('alimentos', AlimentoController::class);
    Route::get('/receitas', [AlimentoController::class, 'receitas'])->middleware('auth')->name('alimentos.receitas');
    Route::get('/receitas/sugestoes', [ReceitaController::class, 'sugerirReceitas'])->middleware(['auth']);
});

require __DIR__.'/auth.php';
