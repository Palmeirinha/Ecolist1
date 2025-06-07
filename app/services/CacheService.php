<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Categoria;
use App\Models\Alimento;
use Carbon\Carbon;

class CacheService
{
    /**
     * Tempo padrão de cache (24 horas)
     */
    const DEFAULT_TTL = 86400;

    /**
     * Retorna todas as categorias com cache
     */
    public static function getCategorias()
    {
        return Cache::remember('categorias', self::DEFAULT_TTL, function () {
            return Categoria::orderBy('nome')->get();
        });
    }

    /**
     * Retorna estatísticas do usuário com cache
     */
    public static function getEstatisticasUsuario($userId)
    {
        $cacheKey = "estatisticas_usuario_{$userId}";
        return Cache::remember($cacheKey, 3600, function () use ($userId) {
            $alimentos = Alimento::where('user_id', $userId)->get();
            
            return [
                'total_alimentos' => $alimentos->count(),
                'proximos_vencer' => $alimentos->where('validade', '<=', Carbon::now()->addDays(7))->count(),
                'por_categoria' => $alimentos->groupBy('categoria_id')
                    ->map(fn($items) => $items->count()),
                'tipos_quantidade' => $alimentos->groupBy('tipo_quantidade')
                    ->map(fn($items) => $items->count())
            ];
        });
    }

    /**
     * Limpa o cache relacionado a um usuário
     */
    public static function limparCacheUsuario($userId)
    {
        Cache::forget("estatisticas_usuario_{$userId}");
        Cache::tags(['alimentos', "user_{$userId}"])->flush();
    }

    /**
     * Limpa o cache de categorias
     */
    public static function limparCacheCategorias()
    {
        Cache::forget('categorias');
    }

    /**
     * Cache de alimentos por categoria
     */
    public static function getAlimentosPorCategoria($userId, $categoriaId)
    {
        $cacheKey = "alimentos_categoria_{$categoriaId}_user_{$userId}";
        return Cache::remember($cacheKey, 3600, function () use ($userId, $categoriaId) {
            return Alimento::with('categoria')
                ->where('user_id', $userId)
                ->where('categoria_id', $categoriaId)
                ->get();
        });
    }
} 