<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

/**
 * Controller para executar comandos Artisan via web na área ADM.
 *
 * Apenas admins podem acessar. Os comandos são predefinidos para evitar execução arbitrária.
 */
class AdmConsoleController extends Controller
{
    /**
     * Comandos predefinidos permitidos.
     */
    protected array $allowedCommands = [
        'migrate' => [
            'name' => 'Rodar migrações',
            'description' => 'Executa as migrações de banco de dados pendentes.',
            'command' => 'migrate',
            'params' => ['--force' => true],
            'dangerous' => false,
        ],
        'migrate:rollback' => [
            'name' => 'Reverter migração',
            'description' => 'Reverte a última migração executada.',
            'command' => 'migrate:rollback',
            'params' => ['--force' => true],
            'dangerous' => true,
        ],
        'db:seed-tmdb' => [
            'name' => 'Popular filmes TMDB',
            'description' => 'Busca até 10.000 filmes da TMDB API e salva no banco com barra de progresso.',
            'command' => 'db:seed-tmdb',
            'params' => ['--max' => 10000],
            'dangerous' => false,
            'ajax' => true,
        ],
        'db:populate-posters' => [
            'name' => 'Buscar capas dos filmes',
            'description' => 'Busca as capas (posters) na TMDB API para os filmes sem imagem.',
            'command' => 'db:populate-posters',
            'params' => [],
            'dangerous' => false,
        ],
        'cache:clear' => [
            'name' => 'Limpar cache',
            'description' => 'Limpa todo o cache da aplicação (config, routes, views).',
            'command' => 'cache:clear',
            'params' => [],
            'dangerous' => false,
        ],
        'storage:link' => [
            'name' => 'Criar link de storage',
            'description' => 'Cria o link simbólico entre storage/app/public e public/storage.',
            'command' => 'storage:link',
            'params' => [],
            'dangerous' => false,
        ],
        'db:seed-users' => [
            'name' => 'Criar usuários admin',
            'description' => 'Cria os 2 usuários administradores (Isaac e Root) com senha padrão.',
            'command' => 'db:seed',
            'params' => ['--class' => 'UsersTableSeeder', '--force' => true],
            'dangerous' => false,
        ],
        'db:dump' => [
            'name' => 'Exportar banco SQL',
            'description' => 'Gera um dump SQL do banco local para download.',
            'command' => 'db:dump',
            'params' => [],
            'dangerous' => false,
        ],
    ];

    /**
     * Exibe a lista de comandos disponíveis.
     */
    public function index(): View
    {
        $commands = $this->allowedCommands;

        return view('adm.console', [
            'commands' => $commands,
            'lastOutput' => session('console_output'),
            'lastExitCode' => session('console_exit_code'),
            'lastCommandName' => session('console_command_name'),
        ]);
    }

    /**
     * Executa um comando predefinido.
     */
    public function execute(Request $request): RedirectResponse
    {
        $commandKey = $request->input('command');

        // Valida que o comando está na lista permitida
        if (! isset($this->allowedCommands[$commandKey])) {
            return redirect()->route('adm.console')
                ->with('console_output', "Comando inválido: {$commandKey}")
                ->with('console_exit_code', 1)
                ->with('console_command_name', $commandKey);
        }

        $commandConfig = $this->allowedCommands[$commandKey];
        $command = $commandConfig['command'];
        $params = $commandConfig['params'];

        // Executa o comando
        Artisan::call($command, $params);
        $output = Artisan::output();
        $exitCode = Artisan::exitCode();

        return redirect()->route('adm.console')
            ->with('console_output', trim($output))
            ->with('console_exit_code', $exitCode)
            ->with('console_command_name', $commandConfig['name']);
    }

    /**
     * Inicia o seed TMDB em background via AJAX.
     */
    public function seedTmdb(Request $request): JsonResponse
    {
        // Limpa progresso anterior
        $progressFile = storage_path('app/tmdb_seed_progress.json');
        if (file_exists($progressFile)) {
            @unlink($progressFile);
        }

        // Inicia o comando em background usando proc_open
        $maxMovies = $request->input('max', 10000);
        $command = sprintf(
            'cd %s && php artisan db:seed-tmdb --max=%d > /dev/null 2>&1 &',
            base_path(),
            (int) $maxMovies
        );
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $process = proc_open($command, $descriptors, $pipes);
        if (is_resource($process)) {
            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
        }

        return response()->json(['status' => 'started', 'message' => 'Seed iniciado em background.']);
    }

    /**
     * Retorna o progresso atual do seed TMDB.
     */
    public function seedTmdbProgress(): JsonResponse
    {
        $progressFile = storage_path('app/tmdb_seed_progress.json');
        if (!file_exists($progressFile)) {
            return response()->json(['status' => 'idle', 'message' => 'Nenhum seed em execução.']);
        }

        $content = file_get_contents($progressFile);
        if (!$content) {
            return response()->json(['status' => 'idle', 'message' => 'Nenhum seed em execução.']);
        }

        $data = json_decode($content, true);
        if (!$data) {
            return response()->json(['status' => 'error', 'message' => 'Erro ao ler progresso.']);
        }

        // Se terminou, limpa o arquivo
        if (($data['status'] ?? '') === 'done') {
            @unlink($progressFile);
        }

        return response()->json($data);
    }

    /**
     * Faz o download do dump SQL gerado pelo comando db:dump.
     */
    public function downloadDump(Request $request): Response|RedirectResponse
    {
        $filepath = session('db_dump_file');
        $filename = session('db_dump_filename');

        if (!$filepath || !file_exists($filepath)) {
            return redirect()->route('adm.console')
                ->with('console_output', 'Nenhum dump SQL encontrado. Execute primeiro o comando "Exportar banco SQL".')
                ->with('console_exit_code', 1)
                ->with('console_command_name', 'Download dump');
        }

        return response()->download($filepath, $filename ?? 'filmes100ads.sql');
    }
}
