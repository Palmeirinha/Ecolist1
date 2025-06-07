<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Alimento;
use App\Models\Categoria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class AlimentoTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $categoria;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->categoria = Categoria::create([
            'nome' => 'Frutas',
            'descricao' => 'Categoria de frutas'
        ]);
    }

    /** @test */
    public function usuario_pode_criar_alimento()
    {
        $response = $this->actingAs($this->user)->post('/alimentos', [
            'nome' => 'Maçã',
            'tipo_quantidade' => 'unidade',
            'quantidade' => 5,
            'validade' => now()->addDays(7)->format('Y-m-d'),
            'categoria_id' => $this->categoria->id
        ]);

        $response->assertRedirect(route('alimentos.index'));
        $this->assertDatabaseHas('alimentos', ['nome' => 'Maçã']);
    }

    /** @test */
    public function valida_quantidade_maxima_por_tipo()
    {
        // Teste para unidades
        $response = $this->actingAs($this->user)->post('/alimentos', [
            'nome' => 'Maçã',
            'tipo_quantidade' => 'unidade',
            'quantidade' => 1000,
            'validade' => now()->addDays(7)->format('Y-m-d'),
            'categoria_id' => $this->categoria->id
        ]);

        $response->assertSessionHasErrors('quantidade');

        // Teste para quilos
        $response = $this->actingAs($this->user)->post('/alimentos', [
            'nome' => 'Maçã',
            'tipo_quantidade' => 'quilo',
            'quantidade' => 101,
            'validade' => now()->addDays(7)->format('Y-m-d'),
            'categoria_id' => $this->categoria->id
        ]);

        $response->assertSessionHasErrors('quantidade');
    }

    /** @test */
    public function valida_data_validade()
    {
        $response = $this->actingAs($this->user)->post('/alimentos', [
            'nome' => 'Maçã',
            'tipo_quantidade' => 'unidade',
            'quantidade' => 5,
            'validade' => now()->addYear()->addDay()->format('Y-m-d'),
            'categoria_id' => $this->categoria->id
        ]);

        $response->assertSessionHasErrors('validade');
    }

    /** @test */
    public function usuario_nao_pode_editar_alimento_de_outro_usuario()
    {
        $outroUsuario = User::factory()->create();
        $alimento = Alimento::factory()->create([
            'user_id' => $outroUsuario->id,
            'categoria_id' => $this->categoria->id
        ]);

        $response = $this->actingAs($this->user)->put("/alimentos/{$alimento->id}", [
            'nome' => 'Novo Nome',
            'tipo_quantidade' => 'unidade',
            'quantidade' => 5,
            'validade' => now()->addDays(7)->format('Y-m-d'),
            'categoria_id' => $this->categoria->id
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function busca_em_tempo_real_funciona()
    {
        Alimento::factory()->create([
            'nome' => 'Maçã Verde',
            'user_id' => $this->user->id,
            'categoria_id' => $this->categoria->id
        ]);

        Alimento::factory()->create([
            'nome' => 'Banana Prata',
            'user_id' => $this->user->id,
            'categoria_id' => $this->categoria->id
        ]);

        $response = $this->actingAs($this->user)->get('/alimentos/buscar?q=maçã');
        
        $response->assertSuccessful();
        $response->assertSee('Maçã Verde');
        $response->assertDontSee('Banana Prata');
    }

    /** @test */
    public function cache_esta_funcionando()
    {
        $alimento = Alimento::factory()->create([
            'user_id' => $this->user->id,
            'categoria_id' => $this->categoria->id
        ]);

        // Primeira requisição (deve ir ao banco)
        $response1 = $this->actingAs($this->user)
            ->get("/alimentos/categoria/{$this->categoria->id}");

        // Segunda requisição (deve vir do cache)
        $response2 = $this->actingAs($this->user)
            ->get("/alimentos/categoria/{$this->categoria->id}");

        $this->assertTrue(
            $response1->getContent() === $response2->getContent()
        );
    }
} 