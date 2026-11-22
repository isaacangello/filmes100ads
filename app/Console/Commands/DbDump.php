<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class DbDump extends Command
{
    /**
     * O nome e sinal do comando.
     *
     * @var string
     */
    protected $signature = 'db:dump';

    /**
     * A descrição do comando.
     *
     * @var string
     */
    protected $description = 'Exporta o banco de dados local como arquivo SQL para download';

    /**
     * Executa o comando.
     */
    public function handle(): int
    {
        $dumpDir = storage_path('app/dumps');
        if (! is_dir($dumpDir)) {
            mkdir($dumpDir, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "filmes100ads_{$timestamp}.sql";
        $filepath = $dumpDir . '/' . $filename;

        $this->info("Exportando banco de dados...");

        // Tenta usar mysqldump via Docker (ambiente local)
        $dbHost = env('DB_HOST', '127.0.0.1');
        $dbUser = env('DB_USERNAME', 'root');
        $dbPass = env('DB_PASSWORD', 'password');
        $dbName = env('DB_DATABASE', 'filmes100ads');

        // Se estiver usando Docker, executa mysqldump dentro do container
        if ($dbHost === '127.0.0.1' || $dbHost === 'localhost') {
            // Tenta detectar se o banco está no Docker
            $containerName = 'mysql_db';
            $dockerCheck = @shell_exec("docker inspect --format='{{.State.Running}}' {$containerName} 2>/dev/null");
            
            if (trim($dockerCheck) === 'true') {
                $dumpCmd = "docker exec {$containerName} mysqldump --skip-comments --skip-lock-tables -u {$dbUser} -p{$dbPass} {$dbName} 2>/dev/null > {$filepath}";
            } else {
                // Banco local sem Docker
                $dumpCmd = "mysqldump --skip-comments --skip-lock-tables -h {$dbHost} -u {$dbUser} -p{$dbPass} {$dbName} > {$filepath} 2>/dev/null";
            }
        } else {
            // Banco remoto
            $dumpCmd = "mysqldump --skip-comments --skip-lock-tables -h {$dbHost} -u {$dbUser} -p{$dbPass} {$dbName} > {$filepath} 2>/dev/null";
        }

        $exitCode = 0;
        shell_exec($dumpCmd . ' ; echo $?');
        
        if (! file_exists($filepath) || filesize($filepath) === 0) {
            $this->error('Falha ao exportar o banco de dados.');
            return Command::FAILURE;
        }

        $size = filesize($filepath);
        $sizeHuman = $this->humanFilesize($size);

        $this->info("Dump criado: {$filename} ({$sizeHuman})");
        $this->info("Local: {$filepath}");

        // Salva o caminho no session para o controller usar
        session(['db_dump_file' => $filepath, 'db_dump_filename' => $filename]);

        $this->info("Use o link de download no console ADM para baixar o arquivo.");

        return Command::SUCCESS;
    }

    /**
     * Formata o tamanho do arquivo em formato legível.
     */
    protected function humanFilesize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 1) . ' ' . $units[$i];
    }
}
