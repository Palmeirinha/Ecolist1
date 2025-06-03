<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AlertaVencimentoAlimentos extends Command
{
    /**
     * O nome e assinatura do comando no terminal.
     *
     * @var string
     */
    protected $signature = 'alerta:vencimento-alimentos';

    /**
     * A descrição do comando no terminal.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Executa o comando.
     */
    public function handle()
    {
        $dias = 3; // Número de dias antes do vencimento para alertar

        // Busca alimentos que ainda não foram alertados e que vencem nos próximos $dias dias
        $alimentos = \App\Models\Alimento::where('alertado', false)
            ->whereDate('validade', '<=', now()->addDays($dias))
            ->whereDate('validade', '>=', now())
            ->get();

        // Para cada alimento encontrado
        foreach ($alimentos as $alimento) {
            $user = $alimento->user; // Obtém o usuário dono do alimento

            // Envia o e-mail de alerta de vencimento (utiliza uma Mailable)
            \Mail::to($user->email)->send(new \App\Mail\AlertaVencimentoAlimento($alimento));

            // Marca o alimento como alertado para não enviar novamente
            $alimento->alertado = true;
            $alimento->save();
        }

        // Exibe mensagem de sucesso no terminal
        $this->info('Alertas de vencimento enviados!');
    }
}
