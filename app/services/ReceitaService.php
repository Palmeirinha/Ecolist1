<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class ReceitaService
{
    public function buscarReceitas($ingrediente)
    {
        $response = Http::get("https://www.themealdb.com/api/json/v1/1/filter.php", [
            'i' => $ingrediente
        ]);
        return $response->json()['meals'] ?? [];
    }
}