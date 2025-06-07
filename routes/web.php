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
    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/perfil', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('alimentos', AlimentoController::class);
    Route::get('/receitas', [AlimentoController::class, 'buscarReceitas'])->name('alimentos.receitas');
    Route::get('/receitas/buscar', [ReceitaController::class, 'buscar'])->name('receitas.buscar');
    Route::get('/receitas/sugestoes', [ReceitaController::class, 'sugerirReceitas']);
});

require __DIR__.'/auth.php';
