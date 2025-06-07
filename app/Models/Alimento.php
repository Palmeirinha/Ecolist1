<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Categoria;
use Carbon\Carbon;

/**
 * Modelo que representa um alimento no sistema
 * 
 * @property int $id
 * @property string $nome
 * @property float $quantidade
 * @property string $tipo_quantidade
 * @property \DateTime $validade
 * @property int $categoria_id
 * @property int $user_id
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 * @property \DateTime $deleted_at
 */
class Alimento extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nome da tabela associada ao modelo
     *
     * @var string
     */
    protected $table = 'alimentos';

    /**
     * Atributos que podem ser preenchidos em massa
     *
     * @var array
     */
    protected $fillable = [
        'nome',
        'quantidade',
        'tipo_quantidade',
        'validade',
        'categoria_id',
        'user_id'
    ];

    /**
     * Atributos que devem ser convertidos para tipos nativos
     *
     * @var array
     */
    protected $casts = [
        'validade' => 'datetime',
        'quantidade' => 'float'
    ];

    /**
     * Define o relacionamento com a categoria do alimento
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    /**
     * Define o relacionamento com o usuário proprietário
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calcula quantos dias faltam para o alimento vencer
     *
     * @return string
     */
    public function getDiasRestantesAttribute()
    {
        if (!$this->validade) {
            return '0 dias';
        }

        $hoje = Carbon::now()->startOfDay();
        $validade = Carbon::parse($this->validade)->startOfDay();
        $dias = $hoje->diffInDays($validade, false);

        if ($dias == 0) {
            return 'Vencendo Hoje';
        } elseif ($dias < 0) {
            return abs($dias) . ' dias (Vencido)';
        } else {
            return $dias . ' dias';
        }
    }

    /**
     * Verifica se o alimento está vencido
     *
     * @return bool
     */
    public function getVencidoAttribute()
    {
        if (!$this->validade) {
            return false;
        }

        return Carbon::parse($this->validade)->startOfDay()->isPast();
    }

    /**
     * Verifica se o alimento está próximo do vencimento (3 dias ou menos)
     *
     * @return bool
     */
    public function getVencendoAttribute()
    {
        if (!$this->validade) {
            return false;
        }

        $hoje = Carbon::now()->startOfDay();
        $validade = Carbon::parse($this->validade)->startOfDay();
        $dias = $hoje->diffInDays($validade, false);
        return $dias >= 0 && $dias <= 3;
    }

    /**
     * Retorna o status do alimento (Vencido, Vencendo ou OK)
     *
     * @return string
     */
    public function getStatusAttribute()
    {
        if ($this->vencido) {
            return 'Vencido';
        }

        if ($this->vencendo) {
            return 'Vencendo';
        }

        return 'OK';
    }

    // Escopo: alimentos vencidos
    public function scopeVencidos($query)
    {
        return $query->whereDate('validade', '<', Carbon::now()->startOfDay());
    }

    // Escopo: alimentos vencendo
    public function scopeVencendo($query)
    {
        return $query->whereDate('validade', '<=', Carbon::now()->addDays(3)->startOfDay())
                    ->whereDate('validade', '>=', Carbon::now()->startOfDay());
    }

    // Escopo: alimentos normais
    public function scopeNormais($query)
    {
        return $query->whereDate('validade', '>', Carbon::now()->addDays(3)->startOfDay());
    }

    // Boot: define eventos do modelo
    protected static function boot()
    {
        parent::boot();

        // Antes de salvar
        static::saving(function ($alimento) {
            // Garante que a quantidade seja positiva
            if ($alimento->quantidade < 0) {
                $alimento->quantidade = 0;
            }

            // Limita o tamanho do nome
            if (strlen($alimento->nome) > 255) {
                $alimento->nome = substr($alimento->nome, 0, 255);
            }
        });
    }
}
