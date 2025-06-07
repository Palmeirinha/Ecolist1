<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Serviço responsável por buscar e formatar receitas
 * Utiliza cache para otimizar as requisições e formata os dados para exibição
 */
class ReceitaService
{
    /**
     * URL base da API de receitas
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * Tempo de duração do cache em segundos (1 hora)
     *
     * @var int
     */
    protected $cacheTimeout = 3600;

    /**
     * Inicializa o serviço configurando a URL base
     */
    public function __construct()
    {
        $this->baseUrl = config('services.receitas-api.base_url');
    }

    /**
     * Busca receitas baseadas em um termo de pesquisa
     * Utiliza cache para evitar requisições repetidas
     *
     * @param string $termo
     * @return array
     */
    public function buscarReceitas($termo)
    {
        try {
            $cacheKey = 'receitas_busca_' . md5($termo);
            return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($termo) {
                try {
                    // Faz a requisição para a API
                    $response = Http::get("{$this->baseUrl}/receitas/todas");

                    if (!$response->successful()) {
                        Log::error('Erro ao buscar receitas', [
                            'termo' => $termo,
                            'status' => $response->status(),
                            'erro' => $response->body()
                        ]);
                        return [];
                    }

                    $todasReceitas = $response->json() ?? [];
                    
                    if (empty($todasReceitas)) {
                        Log::warning('Nenhuma receita encontrada na API', ['termo' => $termo]);
                        return [];
                    }

                    // Filtra as receitas que contêm o termo buscado
                    $receitas = collect($todasReceitas)->filter(function ($receita) use ($termo) {
                        if (!isset($receita['receita']) || !isset($receita['ingredientes'])) {
                            return false;
                        }
                        return str_contains(strtolower($receita['receita']), strtolower($termo)) ||
                               str_contains(strtolower($receita['ingredientes']), strtolower($termo));
                    })->take(12)->values();

                    // Formata cada receita encontrada
                    return $receitas->map(function ($receita) {
                        return $this->formatarReceita($receita);
                    })->all();
                } catch (\Exception $e) {
                    Log::error('Erro ao processar resposta da API', [
                        'termo' => $termo,
                        'erro' => $e->getMessage()
                    ]);
                    return [];
                }
            });
        } catch (\Exception $e) {
            Log::error('Erro inesperado ao buscar receitas', [
                'termo' => $termo,
                'erro' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Busca os detalhes de uma receita específica
     *
     * @param string $id
     * @return array|null
     */
    public function buscarDetalhesReceita($id)
    {
        try {
            $cacheKey = 'receita_detalhes_' . $id;
            return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($id) {
                $response = Http::get("{$this->baseUrl}/receitas/{$id}");

                if (!$response->successful()) {
                    Log::error('Erro ao buscar detalhes da receita', [
                        'id' => $id,
                        'status' => $response->status(),
                        'erro' => $response->body()
                    ]);
                    return null;
                }

                $receita = $response->json();
                return $this->formatarReceita($receita);
            });
        } catch (\Exception $e) {
            Log::error('Erro inesperado ao buscar detalhes da receita', [
                'id' => $id,
                'erro' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Formata os dados da receita para o padrão do sistema
     *
     * @param array $receita
     * @return array|null
     */
    protected function formatarReceita($receita)
    {
        try {
            // Verifica se os campos obrigatórios existem
            if (!isset($receita['receita']) || !isset($receita['ingredientes']) || !isset($receita['modo_preparo'])) {
                Log::warning('Receita com dados incompletos', ['receita' => $receita]);
                return null;
            }

            // Extrai os ingredientes do texto
            $ingredientes = [];
            if (!empty($receita['ingredientes'])) {
                $ingredientes = array_filter(
                    array_map('trim', explode(',', $receita['ingredientes'])),
                    function($item) { return !empty($item); }
                );
            }
            
            // Estima o tempo de preparo baseado no modo de preparo
            $tempoPreparo = $this->estimarTempoPreparo($receita['modo_preparo'] ?? '');

            // Retorna a receita formatada
            return [
                'id' => $receita['id'] ?? uniqid(),
                'strMeal' => $receita['receita'],
                'strMealThumb' => $receita['link_imagem'] ?? 'https://via.placeholder.com/350x250.png?text=Imagem+não+disponível',
                'strCategory' => $receita['tipo'] ?? 'Não categorizado',
                'strArea' => 'Brasileira',
                'ingredientes' => array_map(function ($ingrediente) {
                    return [
                        'nome' => $ingrediente,
                        'medida' => $this->extrairMedida($ingrediente)
                    ];
                }, $ingredientes),
                'instrucoes' => $receita['modo_preparo'] ?? '',
                'tempoPreparo' => $tempoPreparo,
                'porcoes' => 4,
                'informacaoNutricional' => [
                    'calorias' => 0,
                    'proteinas' => 0,
                    'carboidratos' => 0,
                    'gorduras' => 0,
                ],
                'url' => $receita['link_imagem'] ?? '',
                'vegetariano' => $this->verificarVegetariano($receita['ingredientes'] ?? ''),
                'vegano' => $this->verificarVegano($receita['ingredientes'] ?? ''),
                'semGluten' => $this->verificarSemGluten($receita['ingredientes'] ?? ''),
                'tempoPreparoTexto' => $this->formatarTempoPreparo($tempoPreparo)
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao formatar receita', [
                'receita' => $receita,
                'erro' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Estima o tempo de preparo baseado na complexidade do modo de preparo
     *
     * @param string $modoPreparo
     * @return int
     */
    protected function estimarTempoPreparo($modoPreparo)
    {
        // Estima o tempo baseado na quantidade de passos
        $passos = substr_count($modoPreparo, '.');
        return max(15, min(120, $passos * 10)); // Entre 15 e 120 minutos
    }

    /**
     * Extrai a medida de um ingrediente
     *
     * @param string $ingrediente
     * @return string
     */
    protected function extrairMedida($ingrediente)
    {
        if (empty($ingrediente)) {
            return "A gosto";
        }

        // Padrões comuns de medidas
        $padroes = [
            '/(\d+)\s*(g|kg|ml|l|xícara|xícaras|colher|colheres|unidade|unidades)/i',
            '/(\d+)/i'
        ];

        foreach ($padroes as $padrao) {
            if (preg_match($padrao, $ingrediente, $matches)) {
                return $matches[0];
            }
        }

        return "A gosto";
    }

    /**
     * Verifica se a receita é vegetariana
     *
     * @param string $ingredientes
     * @return bool
     */
    protected function verificarVegetariano($ingredientes)
    {
        if (empty($ingredientes)) {
            return false;
        }

        $carnes = ['carne', 'frango', 'peixe', 'atum', 'bacon', 'presunto', 'salsicha', 'linguiça'];
        return !$this->contemPalavras($ingredientes, $carnes);
    }

    /**
     * Verifica se a receita é vegana
     *
     * @param string $ingredientes
     * @return bool
     */
    protected function verificarVegano($ingredientes)
    {
        if (empty($ingredientes)) {
            return false;
        }

        $naoVeganos = ['carne', 'frango', 'peixe', 'atum', 'bacon', 'presunto', 'leite', 'ovo', 'mel', 'queijo', 'manteiga', 'iogurte'];
        return !$this->contemPalavras($ingredientes, $naoVeganos);
    }

    /**
     * Verifica se a receita é sem glúten
     *
     * @param string $ingredientes
     * @return bool
     */
    protected function verificarSemGluten($ingredientes)
    {
        if (empty($ingredientes)) {
            return false;
        }

        $contemGluten = ['farinha de trigo', 'trigo', 'aveia', 'cevada', 'malte', 'centeio'];
        return !$this->contemPalavras($ingredientes, $contemGluten);
    }

    /**
     * Verifica se um texto contém alguma das palavras especificadas
     *
     * @param string $texto
     * @param array $palavras
     * @return bool
     */
    protected function contemPalavras($texto, $palavras)
    {
        $texto = strtolower($texto ?? '');
        foreach ($palavras as $palavra) {
            if (str_contains($texto, strtolower($palavra))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Formata o tempo de preparo em texto
     *
     * @param int $minutos
     * @return string
     */
    protected function formatarTempoPreparo($minutos)
    {
        if ($minutos < 60) {
            return "{$minutos} minutos";
        }
        $horas = floor($minutos / 60);
        $minutosRestantes = $minutos % 60;
        return $minutosRestantes > 0 
            ? "{$horas}h {$minutosRestantes}min" 
            : "{$horas}h";
    }

    /**
     * Busca receitas em lote para múltiplos termos
     * Otimiza as requisições usando uma única chamada
     *
     * @param array $termos
     * @return array
     */
    public function buscarReceitasEmLote(array $termos)
    {
        try {
            $cacheKey = 'receitas_busca_lote_' . md5(implode('_', $termos));
            return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($termos) {
                try {
                    // Faz uma única requisição para a API
                    $response = Http::get("{$this->baseUrl}/receitas/todas");

                    if (!$response->successful()) {
                        Log::error('Erro ao buscar receitas em lote', [
                            'termos' => $termos,
                            'status' => $response->status(),
                            'erro' => $response->body()
                        ]);
                        return [];
                    }

                    $todasReceitas = $response->json() ?? [];
                    $resultado = [];
                    
                    if (empty($todasReceitas)) {
                        Log::warning('Nenhuma receita encontrada na API', ['termos' => $termos]);
                        return [];
                    }

                    // Para cada termo, encontra a primeira receita correspondente
                    foreach ($termos as $termo) {
                        foreach ($todasReceitas as $receita) {
                            if (!isset($receita['receita']) || !isset($receita['ingredientes'])) {
                                continue;
                            }
                            
                            if (str_contains(strtolower($receita['receita']), strtolower($termo)) ||
                                str_contains(strtolower($receita['ingredientes']), strtolower($termo))) {
                                $resultado[$termo] = $this->formatarReceita($receita);
                                break;
                            }
                        }
                    }

                    return $resultado;
                } catch (\Exception $e) {
                    Log::error('Erro ao processar resposta da API em lote', [
                        'termos' => $termos,
                        'erro' => $e->getMessage()
                    ]);
                    return [];
                }
            });
        } catch (\Exception $e) {
            Log::error('Erro inesperado ao buscar receitas em lote', [
                'termos' => $termos,
                'erro' => $e->getMessage()
            ]);
            return [];
        }
    }
}