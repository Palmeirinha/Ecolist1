<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\CacheService;
use App\Models\User;
use App\Models\Alimento;
use App\Models\Categoria;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CacheServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $categoria;

    protected function setUp(): void
    {
        parent::setUp();
        
        Cache::flush();
        $this->user = User::factory()->create();
        $this->categoria = Categoria::create([
            'nome' => 'Frutas',
            'descricao' => 'Categoria de frutas'
        ]);
    }

    /** @test */
    public function pode_cachear_categorias()
    {
        // Primeira chamada (deve ir ao banco)
        $categorias1 = CacheService::getCategorias();
        
        // Segunda chamada (deve vir do cache)
        $categorias2 = CacheService::getCategorias();
        
        $this->assertEquals($categorias1, $categorias2);
        $this->assertTrue(Cache::has('categorias'));
    }

    /** @test */
    public function pode_cachear_estatisticas()
    {
        Alimento::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'categoria_id' => $this->categoria->id
        ]);

        // Primeira chamada (deve ir ao banco)
        $stats1 = CacheService::getEstatisticasUsuario($this->user->id);
        
        // Segunda chamada (deve vir do cache)
        $stats2 = CacheService::getEstatisticasUsuario($this->user->id);
        
        $this->assertEquals($stats1, $stats2);
        $this->assertTrue(Cache::has("estatisticas_usuario_{$this->user->id}"));
    }

    /** @test */
    public function pode_limpar_cache_usuario()
    {
        CacheService::getEstatisticasUsuario($this->user->id);
        $this->assertTrue(Cache::has("estatisticas_usuario_{$this->user->id}"));
        
        CacheService::limparCacheUsuario($this->user->id);
        $this->assertFalse(Cache::has("estatisticas_usuario_{$this->user->id}"));
    }

    /** @test */
    public function pode_limpar_cache_categorias()
    {
        CacheService::getCategorias();
        $this->assertTrue(Cache::has('categorias'));
        
        CacheService::limparCacheCategorias();
        $this->assertFalse(Cache::has('categorias'));
    }

    /** @test */
    public function pode_cachear_alimentos_por_categoria()
    {
        Alimento::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'categoria_id' => $this->categoria->id
        ]);

        // Primeira chamada (deve ir ao banco)
        $alimentos1 = CacheService::getAlimentosPorCategoria($this->user->id, $this->categoria->id);
        
        // Segunda chamada (deve vir do cache)
        $alimentos2 = CacheService::getAlimentosPorCategoria($this->user->id, $this->categoria->id);
        
        $this->assertEquals($alimentos1, $alimentos2);
        $this->assertTrue(Cache::has("alimentos_categoria_{$this->categoria->id}_user_{$this->user->id}"));
    }
} 