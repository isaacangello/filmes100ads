<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
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
        'db:seed' => [
            'name' => 'Popular filmes TMDB',
            'description' => 'Busca até 20.000 filmes da TMDB API e salva no banco.',
            'command' => 'db:seed',
            'params' => ['--class' => 'TmdbMoviesSeeder', '--force' => true],
            'dangerous' => false,
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
}
