<?php

namespace Database\Factories;

use App\Models\Alimento;
use App\Models\User;
use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AlimentoFactory extends Factory
{
    /**
     * O modelo associado à factory.
     *
     * @var string
     */
    protected $model = Alimento::class;

    /**
     * Define o estado padrão do modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => $this->faker->word,
            'quantidade' => $this->faker->randomFloat(2, 0.1, 10),
            'tipo_quantidade' => $this->faker->randomElement(['kg', 'g', 'l', 'ml', 'unidade']),
            'validade' => $this->faker->dateTimeBetween('now', '+30 days'),
            'user_id' => User::factory(),
            'categoria_id' => Categoria::factory(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    /**
     * Indica que o alimento está próximo do vencimento.
     */
    public function proximoVencimento(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'validade' => Carbon::now()->addDays(rand(1, 7)),
            ];
        });
    }

    /**
     * Indica que o alimento está vencido.
     */
    public function vencido(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'validade' => Carbon::now()->subDays(rand(1, 30)),
            ];
        });
    }
} 