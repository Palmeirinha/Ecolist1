<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AlimentoRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Permite que qualquer usuário autenticado use o request
    }

    public function rules()
    {
        return [
            'nome' => 'required|string|max:255|regex:/^[A-Za-zÀ-ú\s]+$/',
            'tipo_quantidade' => 'required|in:unidade,quilo,litro',
            'quantidade' => [
                'required',
                'integer',
                'min:1',
            ],
            'validade' => 'required|date|after_or_equal:today',
            'categoria_id' => 'required|exists:categorias,id',
        ];
    }

    public function messages()
    {
        return [
            'nome.required' => 'O nome do alimento é obrigatório.',
            'nome.regex' => 'O nome deve conter apenas letras e espaços.',
            'quantidade.min' => 'A quantidade deve ser pelo menos 1.',
            'validade.after_or_equal' => 'A validade deve ser uma data futura.',
            'tipo_quantidade.in' => 'O tipo de quantidade deve ser unidade, quilo ou litro.',
            'categoria_id.required' => 'A categoria é obrigatória.',
            'categoria_id.exists' => 'A categoria selecionada não existe.',
        ];
    }
}
