<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Alimento;
use App\Models\User;
use App\Models\Alerta;
use Carbon\Carbon;

class AlertaVencimentoAlimentos extends Command
{
    /**
     * O nome e a assinatura do comando do console.
     *
     * @var string
     */
    protected $signature = 'alerta:vencimento-alimentos';

    /**
     * A descrição do comando do console.
     *
     * @var string
     */
    protected $description = 'Gera alertas sobre alimentos próximos do vencimento';

    /**
     * Execute o comando do console.
     */
    public function handle()
    {
        $this->info('Iniciando verificação de alimentos próximos do vencimento...');

        // Busca todos os usuários ativos
        $users = User::all();

        foreach ($users as $user) {
            // Busca alimentos que vencem em até 7 dias
            $alimentosProximosVencimento = Alimento::where('user_id', $user->id)
                ->where('validade', '<=', Carbon::now()->addDays(7))
                ->where('validade', '>=', Carbon::now())
                ->whereDoesntHave('alertas', function($query) {
                    $query->where('created_at', '>=', Carbon::now()->subDays(1));
                })
                ->orderBy('validade')
                ->get();

            if ($alimentosProximosVencimento->isNotEmpty()) {
                foreach ($alimentosProximosVencimento as $alimento) {
                    // Cria um alerta para cada alimento
                    Alerta::create([
                        'user_id' => $user->id,
                        'alimento_id' => $alimento->id,
                        'mensagem' => "O alimento {$alimento->nome} vence em {$alimento->dias_restantes} dias",
                        'tipo' => 'vencimento',
                        'lido' => false
                    ]);
                }
                
                $this->info("Alertas gerados para o usuário {$user->name}");
                \Log::info("Alertas de vencimento gerados para o usuário {$user->name}");
            }
        }

        $this->info('Verificação concluída!');
    }
}
