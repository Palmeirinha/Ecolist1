<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use Illuminate\Http\Request;

class AlertaController extends Controller
{
    /**
     * Marca um alerta como lido
     *
     * @param Alerta $alerta
     * @return \Illuminate\Http\RedirectResponse
     */
    public function marcarComoLido(Alerta $alerta)
    {
        // Verifica se o alerta pertence ao usuário atual
        if ($alerta->user_id !== auth()->id()) {
            abort(403);
        }

        $alerta->update(['lido' => true]);

        return back()->with('status', 'Alerta marcado como lido.');
    }

    /**
     * Retorna os alertas não lidos do usuário atual
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAlertasNaoLidos()
    {
        $alertas = Alerta::where('user_id', auth()->id())
            ->where('lido', false)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($alertas);
    }
} 