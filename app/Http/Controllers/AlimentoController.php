<?php
namespace App\Http\Controllers;

use App\Models\Alimento;
use Illuminate\Http\Request;
use App\Services\ReceitaService;
use App\Models\Categoria;
use App\Http\Requests\AlimentoRequest;

/**
 * Controlador responsável pelo gerenciamento de alimentos
 * Implementa as operações CRUD e funcionalidades relacionadas
 */
class AlimentoController extends Controller
{
    /**
     * Exibe a lista de alimentos do usuário
     * Permite filtrar por categoria e inclui sugestões de receitas
     * 
     * @param Request $request
     * @param ReceitaService $receitaService
     * @return \Illuminate\View\View
     */
    public function index(Request $request, ReceitaService $receitaService)
    {
        // Obtém o filtro de categoria se existir
        $categoriaId = $request->input('categoria_id');
        
        // Inicia a query base com relacionamento de categoria
        $query = Alimento::with('categoria')->where('user_id', auth()->id());

        // Aplica filtro por categoria se solicitado
        if ($categoriaId) {
            $query->where('categoria_id', $categoriaId);
        }

        // Executa a query com paginação
        $alimentos = $query->paginate(15);
        
        // Carrega todas as categorias para o filtro
        $categorias = Categoria::orderBy('nome')->get();

        // Busca sugestões de receitas em lote
        $nomes = $alimentos->pluck('nome')->toArray();
        $receitasLote = $receitaService->buscarReceitasEmLote($nomes);

        // Associa as receitas aos alimentos
        foreach ($alimentos as $alimento) {
            if (isset($receitasLote[$alimento->nome])) {
                $alimento->sugestao = $receitasLote[$alimento->nome]['strMeal'];
            }
        }

        return view('alimentos.index', compact('alimentos', 'categorias', 'categoriaId'));
    }

    /**
     * Exibe o formulário para cadastrar um novo alimento
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $categorias = Categoria::orderBy('nome')->get();
        return view('alimentos.create', compact('categorias'));
    }

    /**
     * Salva um novo alimento no banco de dados
     * Inclui validações e associação com o usuário atual
     * 
     * @param AlimentoRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(AlimentoRequest $request)
    {
        try {
            // Cria o alimento com os dados validados
            $alimento = new Alimento($request->validated());
            $alimento->user_id = auth()->id();
            $alimento->save();

            return redirect()
                ->route('alimentos.index')
                ->with('success', 'Alimento cadastrado com sucesso!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao cadastrar alimento. Tente novamente.');
        }
    }

    /**
     * Exibe o formulário para editar um alimento existente
     * 
     * @param Alimento $alimento
     * @return \Illuminate\View\View
     */
    public function edit(Alimento $alimento)
    {
        // Verifica se o alimento pertence ao usuário atual
        if ($alimento->user_id !== auth()->id()) {
            abort(403, 'Acesso não autorizado.');
        }

        // Carrega o relacionamento com categoria
        $alimento->load('categoria');
        
        $categorias = Categoria::orderBy('nome')->get();
        return view('alimentos.edit', compact('alimento', 'categorias'));
    }

    /**
     * Atualiza um alimento existente no banco de dados
     * 
     * @param AlimentoRequest $request
     * @param Alimento $alimento
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(AlimentoRequest $request, Alimento $alimento)
    {
        try {
            // Verifica se o alimento pertence ao usuário atual
            if ($alimento->user_id !== auth()->id()) {
                abort(403, 'Acesso não autorizado.');
            }

            // Atualiza o alimento com os dados validados
            $alimento->update($request->validated());

            return redirect()
                ->route('alimentos.index')
                ->with('success', 'Alimento atualizado com sucesso!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar alimento. Tente novamente.');
        }
    }

    /**
     * Remove um alimento do banco de dados (soft delete)
     * 
     * @param Alimento $alimento
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Alimento $alimento)
    {
        try {
            // Verifica se o alimento pertence ao usuário atual
            if ($alimento->user_id !== auth()->id()) {
                abort(403, 'Acesso não autorizado.');
            }

            // Realiza a exclusão lógica (soft delete)
            $alimento->delete();

            return redirect()
                ->route('alimentos.index')
                ->with('success', 'Alimento removido com sucesso!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erro ao remover alimento. Tente novamente.');
        }
    }

    /**
     * Busca e exibe receitas baseadas nos alimentos do usuário
     * 
     * @param ReceitaService $receitaService
     * @return \Illuminate\View\View
     */
    public function buscarReceitas(ReceitaService $receitaService)
    {
        // Obtém todos os alimentos do usuário
        $alimentos = Alimento::where('user_id', auth()->id())->pluck('nome')->toArray();
        $todasReceitas = [];

        // Busca receitas para cada alimento
        foreach ($alimentos as $alimento) {
            $receitas = $receitaService->buscarReceitas($alimento);
            if (!empty($receitas)) {
                $todasReceitas = array_merge($todasReceitas, $receitas);
            }
        }

        // Remove receitas duplicadas
        $receitasUnicas = collect($todasReceitas)->unique('id')->values()->all();

        return view('alimentos.receitas', ['receitas' => $receitasUnicas]);
    }
}
