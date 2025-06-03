<?php
namespace App\Http\Controllers;

use App\Models\Alimento;
use Illuminate\Http\Request;
use App\Services\ReceitaService;
use App\Models\Categoria;

class AlimentoController extends Controller
{
    /**
     * Exibe a lista de alimentos do usuário, com filtro por categoria.
     */
    public function index(Request $request)
    {
        $categorias = Categoria::all(); // Busca todas as categorias
        $query = Alimento::where('user_id', auth()->id()); // Alimentos do usuário autenticado

        // Aplica filtro por categoria, se informado
        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        $alimentos = $query->get(); // Recupera os alimentos filtrados

        return view('alimentos.index', compact('alimentos', 'categorias'));
    }

    /**
     * Exibe o formulário para cadastrar um novo alimento.
     */
    public function create()
    {
        $categorias = Categoria::all(); // Busca todas as categorias
        return view('alimentos.create', compact('categorias'));
    }

    /**
     * Salva um novo alimento no banco de dados.
     */
    public function store(Request $request)
    {
        // Validação dos dados do formulário
        $request->validate([
            'nome' => 'required|string|max:255',
            'tipo_quantidade' => 'required|in:unidade,quilo',
            'quantidade' => [
                'required',
                'integer',
                'min:1',
                // Validação personalizada para quantidade máxima
                function($attribute, $value, $fail) use ($request) {
                    if ($request->tipo_quantidade === 'quilo' && $value > 10) {
                        $fail('A quantidade em quilos não pode ser maior que 10.');
                    }
                    if ($request->tipo_quantidade === 'unidade' && $value > 100) {
                        $fail('A quantidade em unidades não pode ser maior que 100.');
                    }
                }
            ],
            'validade' => 'required|date|after_or_equal:today',
        ], [
            'nome.required' => 'O nome do alimento é obrigatório.',
            'quantidade.min' => 'A quantidade deve ser pelo menos 1.',
            'validade.after_or_equal' => 'A validade deve ser uma data futura.',
        ]);

        // Verifica se já existe alimento com o mesmo nome para o usuário
        $existe = \App\Models\Alimento::where('user_id', auth()->id())
            ->where('nome', $request->nome)
            ->first();

        if ($existe) {
            // Retorna erro se já existir alimento com o mesmo nome
            return back()
                ->withInput()
                ->withErrors(['nome' => 'Você já cadastrou um alimento com esse nome.']);
        }

        // Cria o alimento
        Alimento::create([
            'user_id' => auth()->id(),
            'nome' => $request->nome,
            'quantidade' => $request->quantidade,
            'validade' => $request->validade,
            'categoria_id' => $request->categoria_id,
        ]);

        return redirect()->route('alimentos.index')->with('success', 'Alimento cadastrado com sucesso!');
    }

    /**
     * Exibe um alimento específico (não implementado).
     */
    public function show(Alimento $alimento)
    {
        //
    }

    /**
     * Exibe o formulário de edição de um alimento.
     */
    public function edit(Alimento $alimento)
    {
        $categorias = Categoria::all(); // Busca todas as categorias
        return view('alimentos.edit', compact('alimento', 'categorias'));
    }

    /**
     * Atualiza os dados de um alimento existente.
     */
    public function update(Request $request, Alimento $alimento)
    {
        // Garante que só o dono pode editar
        if ($alimento->user_id !== auth()->id()) {
            abort(403);
        }

        // Validação dos dados do formulário
        $request->validate([
            'nome' => 'required|string|max:255',
            'tipo_quantidade' => 'required|in:unidade,quilo',
            'quantidade' => [
                'required',
                'integer',
                'min:1',
                // Validação personalizada para quantidade máxima
                function($attribute, $value, $fail) use ($request) {
                    if ($request->tipo_quantidade === 'quilo' && $value > 10) {
                        $fail('A quantidade em quilos não pode ser maior que 10.');
                    }
                    if ($request->tipo_quantidade === 'unidade' && $value > 100) {
                        $fail('A quantidade em unidades não pode ser maior que 100.');
                    }
                }
            ],
            'validade' => 'required|date|after_or_equal:today',
        ], [
            'nome.required' => 'O nome do alimento é obrigatório.',
            'quantidade.min' => 'A quantidade deve ser pelo menos 1.',
            'validade.after_or_equal' => 'A validade deve ser uma data futura.',
        ]);

        // Atualiza o alimento
        $alimento->update([
            'nome' => $request->nome,
            'quantidade' => $request->quantidade,
            'validade' => $request->validade,
            'categoria_id' => $request->categoria_id,
        ]);

        return redirect()->route('alimentos.index')->with('success', 'Alimento atualizado com sucesso!');
    }

    /**
     * Remove um alimento do banco de dados.
     */
    public function destroy(Alimento $alimento)
    {
        // Garante que só o dono pode excluir
        if ($alimento->user_id !== auth()->id()) {
            abort(403);
        }

        $alimento->delete(); // Exclui o alimento

        return redirect()->route('alimentos.index')->with('success', 'Alimento excluído com sucesso!');
    }

    /**
     * Exibe receitas baseadas nos alimentos do usuário.
     */
    public function receitas(ReceitaService $receitaService)
    {
        $alimentos = Alimento::where('user_id', auth()->id())->pluck('nome')->toArray(); // Nomes dos alimentos do usuário
        $receitas = [];
        // Busca receitas para cada alimento
        foreach ($alimentos as $alimento) {
            $receitas = array_merge($receitas, $receitaService->buscarReceitas($alimento));
        }
        return view('alimentos.receitas', compact('receitas'));
    }
}
