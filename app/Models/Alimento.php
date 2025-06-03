<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Categoria;

class Alimento extends Model
{
    protected $fillable = ['user_id', 'nome', 'quantidade', 'validade', 'alertado', 'tipo_quantidade'];

    // Relacionamento: cada alimento pertence a um usuário
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relacionamento: cada alimento pertence a uma categoria
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    // Acessor: retorna uma sugestão de receita
    public function getReceitaSugeridaAttribute()
    {
        return "Use {$this->nome} para fazer um prato delicioso!";
    }
}
