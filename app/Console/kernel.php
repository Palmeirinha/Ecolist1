<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

// Classe Kernel responsável pelo agendamento e registro de comandos do console
class Kernel extends ConsoleKernel
{
    /**
     * Comandos customizados registrados manualmente.
     */
    protected $commands = [
        \App\Console\Commands\AlertaVencimentoAlimentos::class,
    ];

    /**
     * Define o agendamento dos comandos.
     * Aqui você pode agendar tarefas para serem executadas periodicamente.
     */
    protected function schedule(Schedule $schedule)
    {
        // Agenda o comando 'alerta:vencimento-alimentos' para rodar diariamente
        $schedule->command('alerta:vencimento-alimentos')->daily();
    }

    /**
     * Registra os comandos do aplicativo.
     * Carrega comandos personalizados e inclui o arquivo de rotas do console.
     */
    protected function commands()
    {
        // Carrega todos os comandos personalizados do diretório Commands
        $this->load(__DIR__.'/Commands');
        // Inclui as rotas de comandos definidas em routes/console.php
        require base_path('routes/console.php');
    }
}