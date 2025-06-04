<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReceitaService;

class ReceitaController extends Controller
{
    public function sugerirReceitas(Request $request, ReceitaService $receitaService)
    {
        $ingrediente = $request->input('ingrediente', 'frango');
        $receitas = $receitaService->buscarReceitas($ingrediente);

        return view('receitas.sugestoes', compact('receitas', 'ingrediente'));
    }
}
