<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Alimento;
use App\Models\User;
use App\Models\Categoria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AlimentoTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $categoria;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Cria um usuário e uma categoria para os testes
        $this->user = User::factory()->create();
        $this->categoria = Categoria::factory()->create();
    }

    /** @test */
    public function pode_criar_um_alimento()
    {
        $alimento = Alimento::factory()->create([
            'user_id' => $this->user->id,
            'categoria_id' => $this->categoria->id,
            'nome' => 'Maçã',
            'quantidade' => 1.5,
            'tipo_quantidade' => 'kg',
            'validade' => Carbon::now()->addDays(5)
        ]);

        $this->assertDatabaseHas('alimentos', [
            'id' => $alimento->id,
            'nome' => 'Maçã'
        ]);
    }

    /** @test */
    public function calcula_dias_restantes_corretamente()
    {
        $alimento = Alimento::factory()->create([
            'user_id' => $this->user->id,
            'categoria_id' => $this->categoria->id,
            'validade' => Carbon::now()->addDays(5)
        ]);

        $this->assertEquals(5, $alimento->dias_restantes);
    }

    /** @test */
    public function identifica_alimento_proximo_do_vencimento()
    {
        $alimento = Alimento::factory()->create([
            'user_id' => $this->user->id,
            'categoria_id' => $this->categoria->id,
            'validade' => Carbon::now()->addDays(3)
        ]);

        $this->assertTrue($alimento->isProximoVencer(7));
        $this->assertFalse($alimento->isVencido());
    }

    /** @test */
    public function identifica_alimento_vencido()
    {
        $alimento = Alimento::factory()->create([
            'user_id' => $this->user->id,
            'categoria_id' => $this->categoria->id,
            'validade' => Carbon::now()->subDays(1)
        ]);

        $this->assertTrue($alimento->isVencido());
        $this->assertFalse($alimento->isProximoVencer(7));
    }

    /** @test */
    public function pertence_ao_usuario_correto()
    {
        $alimento = Alimento::factory()->create([
            'user_id' => $this->user->id,
            'categoria_id' => $this->categoria->id
        ]);

        $this->assertTrue($alimento->user->is($this->user));
    }

    /** @test */
    public function pertence_a_categoria_correta()
    {
        $alimento = Alimento::factory()->create([
            'user_id' => $this->user->id,
            'categoria_id' => $this->categoria->id
        ]);

        $this->assertTrue($alimento->categoria->is($this->categoria));
    }
} 