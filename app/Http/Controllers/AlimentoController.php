<?php
namespace App\Http\Controllers;

use App\Models\Alimento;
use Illuminate\Http\Request;
use App\Services\ReceitaService;
use App\Models\Categoria;
use App\Http\Requests\AlimentoRequest;

class AlimentoController extends Controller
{
    /**
     * Exibe a lista de alimentos do usuário, com filtro por categoria.
     */
    public function index(Request $request, ReceitaService $receitaService)
    {
        $categoriaId = $request->input('categoria_id');
        $query = Alimento::where('user_id', auth()->id()); // Garante que só mostra alimentos do usuário

        if ($categoriaId) {
            $query->where('categoria_id', $categoriaId);
        }

        $alimentos = $query->get();

        foreach ($alimentos as $alimento) {
            $receitas = $receitaService->buscarReceitas($alimento->nome);
            $alimento->sugestao = $receitas[0]['strMeal'] ?? null;
        }

        $categorias = Categoria::all();

        return view('alimentos.index', compact('alimentos', 'categorias', 'categoriaId'));
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
    public function store(AlimentoRequest $request)
    {
        // --- INÍCIO DA VERIFICAÇÃO DE NOME X CATEGORIA ---
        $mapaCategoria = [
            'frutas' => ['maçã', 'banana', 'melancia', 'limão', 'laranja', 'manga', 'uva', 'abacaxi', 'goiaba', 'morango', 'kiwi', 'pera', 'pêssego', 'ameixa', 'caju', 'graviola', 'acerola', 'framboesa', 'maracujá', 'figo'],
            'verduras' => ['alface', 'rúcula', 'espinafre', 'couve', 'agrião', 'repolho', 'acelga', 'radite', 'mostarda', 'almeirão', 'endívia', 'chicória', 'escarola', 'ervilha'],
            'legumes' => ['cenoura', 'batata', 'abobrinha', 'pepino', 'chuchu', 'berinjela', 'beterraba', 'mandioca', 'inhame', 'cará', 'abóbora', 'pimentão', 'tomate', 'milho'],
            'carnes' => ['carne', 'bovina', 'porco', 'lombo', 'frango', 'filé', 'picanha', 'costela', 'moída', 'linguiça', 'pernil', 'alcatra', 'maminha', 'peito', 'coxinha', 'coxa', 'tilápia', 'salmão', 'atum', 'peixe'],
            'bebidas' => ['água', 'refrigerante', 'suco', 'cerveja', 'vinho', 'chá', 'café', 'achocolatado', 'milkshake', 'isotônico', 'energético', 'licor', 'leite', 'vodka', 'rum', 'whisky'],
        ];

        $nome = strtolower($request->nome);
        $categoria = \App\Models\Categoria::find($request->categoria_id);

        if ($categoria) {
            $categoriaNome = strtolower($categoria->nome);

            if (isset($mapaCategoria[$categoriaNome])) {
                $permitidos = $mapaCategoria[$categoriaNome];
                $encontrou = false;

                foreach ($permitidos as $palavra) {
                    if (str_contains($nome, $palavra)) {
                        $encontrou = true;
                        break;
                    }
                }

                if (!$encontrou) {
                    return back()->withInput()->withErrors([
                        'nome' => 'O nome do alimento não condiz com a categoria "' . $categoria->nome . '".'
                    ]);
                }
            }
        }
        // --- FIM DA VERIFICAÇÃO DE NOME X CATEGORIA ---

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

        $categoria = \App\Models\Categoria::find($request->categoria_id);

        if ($categoria && strtolower($categoria->nome) === 'bebidas') {
            if ($request->tipo_quantidade === 'quilo') {
                return back()->withInput()->withErrors([
                    'tipo_quantidade' => 'O tipo de quantidade "quilo" não é permitido para bebidas. Por favor, escolha "unidade" ou "litro".'
                ]);
            }
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
    public function update(AlimentoRequest $request, Alimento $alimento)
    {
        // Garante que só o dono pode editar
        if ($alimento->user_id !== auth()->id()) {
            abort(403);
        }

        // --- INÍCIO DA VERIFICAÇÃO DE NOME X CATEGORIA ---
        $mapaCategoria = [
            'frutas' => ['maçã', 'banana', 'melancia', 'limão', 'laranja', 'manga', 'uva', 'abacaxi', 'goiaba', 'morango', 'kiwi', 'pera', 'pêssego', 'ameixa', 'caju', 'graviola', 'acerola', 'framboesa', 'maracujá', 'figo'],
            'verduras' => ['alface', 'rúcula', 'espinafre', 'couve', 'agrião', 'repolho', 'acelga', 'radite', 'mostarda', 'almeirão', 'endívia', 'chicória', 'escarola', 'ervilha'],
            'legumes' => ['cenoura', 'batata', 'abobrinha', 'pepino', 'chuchu', 'berinjela', 'beterraba', 'mandioca', 'inhame', 'cará', 'abóbora', 'pimentão', 'tomate', 'milho'],
            'carnes' => ['carne', 'bovina', 'porco', 'lombo', 'frango', 'filé', 'picanha', 'costela', 'moída', 'linguiça', 'pernil', 'alcatra', 'maminha', 'peito', 'coxinha', 'coxa', 'tilápia', 'salmão', 'atum', 'peixe'],
            'bebidas' => ['água', 'refrigerante', 'suco', 'cerveja', 'vinho', 'chá', 'café', 'achocolatado', 'milkshake', 'isotônico', 'energético', 'licor', 'leite', 'vodka', 'rum', 'whisky'],
        ];

        $nome = strtolower($request->nome);
        $categoria = \App\Models\Categoria::find($request->categoria_id);

        if ($categoria) {
            $categoriaNome = strtolower($categoria->nome);

            if (isset($mapaCategoria[$categoriaNome])) {
                $permitidos = $mapaCategoria[$categoriaNome];
                $encontrou = false;

                foreach ($permitidos as $palavra) {
                    if (str_contains($nome, $palavra)) {
                        $encontrou = true;
                        break;
                    }
                }

                if (!$encontrou) {
                    return back()->withInput()->withErrors([
                        'nome' => 'O nome do alimento não condiz com a categoria "' . $categoria->nome . '".'
                    ]);
                }
            }
        }
        // --- FIM DA VERIFICAÇÃO DE NOME X CATEGORIA ---

        $categoria = \App\Models\Categoria::find($request->categoria_id);

        if ($categoria && strtolower($categoria->nome) === 'bebidas') {
            if ($request->tipo_quantidade === 'quilo') {
                return back()->withInput()->withErrors([
                    'tipo_quantidade' => 'O tipo de quantidade "quilo" não é permitido para bebidas. Por favor, escolha "unidade" ou "litro".'
                ]);
            }
        }

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
