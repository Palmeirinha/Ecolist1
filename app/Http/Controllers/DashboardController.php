<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $total = \App\Models\Alimento::where('user_id', $userId)->count();
        $vencendo = \App\Models\Alimento::where('user_id', $userId)
            ->whereDate('validade', '<=', now()->addDays(3))
            ->whereDate('validade', '>=', now())
            ->count();
        $vencidos = \App\Models\Alimento::where('user_id', $userId)
            ->whereDate('validade', '<', now())
            ->count();

        return view('dashboard', compact('total', 'vencendo', 'vencidos'));
    }
}
