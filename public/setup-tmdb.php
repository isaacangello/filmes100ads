<?php
/**
 * Script temporário para popular o banco com filmes da TMDB API.
 *
 * Acessível apenas via navegador (resolve o anti-bot do InfinityFree).
 * Auto-destrói após execução. Protegido por DEPLOY_KEY.
 *
 * Uso: http://filmes100ads.free.nf/setup-tmdb.php?key=SEU_DEPLOY_KEY&action=seed
 */

// Carrega o Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$self = __FILE__;

// Lê a DEPLOY_KEY do .env (ou env.production) manualmente, já que config() pode não estar carregado
$deployKey = '';
$envFile = __DIR__ . '/../.env';
if (!file_exists($envFile)) {
    $envFile = __DIR__ . '/../env.production';
}
if (file_exists($envFile)) {
    foreach (file($envFile) as $line) {
        if (preg_match('/^DEPLOY_KEY\s*=\s*"?(.+?)"?\s*$/', trim($line), $m)) {
            $deployKey = $m[1];
            break;
        }
    }
}

$key = $_GET['key'] ?? '';
$action = $_GET['action'] ?? '';

// Valida a chave
if ($key !== $deployKey) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Chave inválida.']);
    exit;
}

// Limite de tempo estendido
set_time_limit(0);
ignore_user_abort(true);

$output = '';
$errors = [];

if ($action === 'seed') {
    try {
        $maxMovies = (int) ($_GET['max'] ?? 10000);
        
        $output .= "Iniciando seed de {$maxMovies} filmes da TMDB API...\n";
        
        // Executa o comando via Artisan
        $exitCode = \Illuminate\Support\Facades\Artisan::call('db:seed-tmdb', ['--max' => $maxMovies]);
        $output .= \Illuminate\Support\Facades\Artisan::output();
        $output .= "Exit code: {$exitCode}\n";
        
        // Limpa caches
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        $output .= "Caches limpos.\n";
        
        $status = $exitCode === 0 ? 'success' : 'error';
    } catch (\Exception $e) {
        $status = 'error';
        $errors[] = $e->getMessage();
        $output .= "ERRO: " . $e->getMessage() . "\n";
    }
} elseif ($action === 'check') {
    $status = 'ok';
    $output = "Script TMDB pronto. Use action=seed para iniciar.\n";
    $output .= "PHP: " . PHP_VERSION . "\n";
    $output .= "TMDB_API_KEY: " . (config('services.tmdb.api_key') ? 'configurado' : 'NÃO configurado') . "\n";
} else {
    $status = 'error';
    $errors[] = 'Ação inválida. Use action=seed ou action=check.';
}

// Auto-destrói o script
@unlink($self);

http_response_code(200);
header('Content-Type: application/json');
echo json_encode([
    'status' => $status,
    'action' => $action,
    'output' => $output,
    'errors' => $errors,
], JSON_UNESCAPED_UNICODE);
