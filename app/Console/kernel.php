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
     * Define o cronograma de comandos da aplicação.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Executa o backup do banco de dados todos os dias às 01:00
        $schedule->command('backup:database')
                ->dailyAt('01:00')
                ->appendOutputTo(storage_path('logs/backup.log'));

        // Limpa o cache todos os dias às 00:00
        $schedule->command('cache:clear')->daily();

        // Remove alimentos excluídos há mais de 30 dias (exclusão permanente)
        $schedule->command('model:prune', [
            '--model' => [App\Models\Alimento::class],
            '--days' => 30,
        ])->daily();
    }

    /**
     * Registra os comandos da aplicação.
     */
    protected function commands(): void
    {
        // Carrega todos os comandos personalizados do diretório Commands
        $this->load(__DIR__.'/Commands');
        // Inclui as rotas de comandos definidas em routes/console.php
        require base_path('routes/console.php');
    }
}