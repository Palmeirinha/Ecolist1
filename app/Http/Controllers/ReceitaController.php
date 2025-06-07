<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReceitaService;

/**
 * Controlador responsável pela busca e exibição de receitas
 * Utiliza o ReceitaService para interagir com a API de receitas
 */
class ReceitaController extends Controller
{
    /**
     * Busca receitas com base em um termo de pesquisa
     * 
     * @param Request $request
     * @param ReceitaService $receitaService
     * @return \Illuminate\View\View
     */
    public function buscar(Request $request, ReceitaService $receitaService)
    {
        // Obtém o termo de busca da requisição
        $termo = $request->input('termo');
        $receitas = [];

        // Se houver um termo, busca as receitas
        if ($termo) {
            $receitas = $receitaService->buscarReceitas($termo);
        }

        return view('receitas.buscar', compact('receitas', 'termo'));
    }

    /**
     * Sugere receitas com base em um ingrediente específico
     * 
     * @param Request $request
     * @param ReceitaService $receitaService
     * @return \Illuminate\View\View
     */
    public function sugerirReceitas(Request $request, ReceitaService $receitaService)
    {
        // Obtém o ingrediente da requisição
        $ingrediente = $request->input('ingrediente');
        $receitas = [];

        // Se houver um ingrediente, busca receitas relacionadas
        if ($ingrediente) {
            $receitas = $receitaService->buscarReceitas($ingrediente);
        }

        return view('receitas.sugestoes', compact('receitas', 'ingrediente'));
    }
}
