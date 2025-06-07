<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    /**
     * O nome e a assinatura do comando do console.
     *
     * @var string
     */
    protected $signature = 'backup:database';

    /**
     * A descrição do comando do console.
     *
     * @var string
     */
    protected $description = 'Cria um backup do banco de dados';

    /**
     * Execute o comando do console.
     */
    public function handle()
    {
        // Cria o diretório de backup se não existir
        if (!Storage::exists('backups')) {
            Storage::makeDirectory('backups');
        }

        // Nome do arquivo de backup
        $filename = 'backup_' . Carbon::now()->format('Y-m-d_H-i-s') . '.sql';

        // Configurações do banco de dados
        $host = config('database.connections.mysql.host');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        // Comando para criar o backup
        $command = sprintf(
            'mysqldump -h %s -u %s -p%s %s > %s',
            $host,
            $username,
            $password,
            $database,
            storage_path('app/backups/' . $filename)
        );

        try {
            // Executa o comando de backup
            exec($command);

            // Mantém apenas os últimos 7 backups
            $files = Storage::files('backups');
            if (count($files) > 7) {
                $oldestFiles = array_slice($files, 0, count($files) - 7);
                foreach ($oldestFiles as $file) {
                    Storage::delete($file);
                }
            }

            $this->info('Backup criado com sucesso: ' . $filename);
            
            // Registra o backup no log
            \Log::info('Backup do banco de dados criado: ' . $filename);
        } catch (\Exception $e) {
            $this->error('Erro ao criar backup: ' . $e->getMessage());
            \Log::error('Erro ao criar backup do banco de dados: ' . $e->getMessage());
        }
    }
} 