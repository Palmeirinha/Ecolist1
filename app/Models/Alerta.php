<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Alerta extends Model
{
    use HasFactory;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'alertas';

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'alimento_id',
        'mensagem',
        'tipo',
        'lido'
    ];

    /**
     * Os atributos que devem ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'lido' => 'boolean',
    ];

    /**
     * Obtém o usuário associado ao alerta.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtém o alimento associado ao alerta.
     */
    public function alimento()
    {
        return $this->belongsTo(Alimento::class);
    }
} 