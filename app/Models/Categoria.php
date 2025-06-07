<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo que representa uma categoria de alimentos
 * 
 * @property int $id
 * @property string $nome
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 */
class Categoria extends Model
{
    use HasFactory;

    /**
     * Nome da tabela associada ao modelo
     *
     * @var string
     */
    protected $table = 'categorias';

    /**
     * Atributos que podem ser preenchidos em massa
     *
     * @var array
     */
    protected $fillable = ['nome'];

    /**
     * Define o relacionamento com os alimentos desta categoria
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function alimentos()
    {
        return $this->hasMany(Alimento::class);
    }
}
