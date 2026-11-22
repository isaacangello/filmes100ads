<?php
/**
 * filmes100ads — Deploy Setup
 *
 * Combined script: extract vendor.zip + run migrations + cleanup.
 * Designed for shared hosting without SSH (InfinityFree).
 *
 * IMPORTANT: InfinityFree anti-bot blocks curl requests.
 * This script MUST be accessed via browser to work.
 *
 * STRUCTURE (P4O pattern): Everything lives inside htdocs/.
 *   /filmes100ads.free.nf/htdocs/
 *     .htaccess          — RewriteRule ^(.*)$ public/$1 [L]
 *     .env               — Laravel env file
 *     app/, config/, vendor/, etc. — Laravel root
 *     public/            — Laravel public dir (this script lives here)
 *       deploy-setup.php — This file
 *
 * Usage:
 *   ?key=YOUR_KEY&action=setup    — extract vendor.zip + migrate + cleanup
 *   ?key=YOUR_KEY&action=migrate  — run pending migrations only
 *   ?key=YOUR_KEY&action=fresh    — migrate:fresh + db:seed
 *   ?key=YOUR_KEY&action=seed     — migrate + db:seed
 *   ?key=YOUR_KEY&action=check    — show environment info
 */

header('Content-Type: application/json');

// P4O pattern: tudo dentro de htdocs/
// deploy-setup.php está em htdocs/public/
// $root = htdocs/ (onde fica .env, vendor/, app/, etc.)
$root = dirname(__DIR__);
$envFile = $root . '/.env';

$DEPLOY_KEY = getenv('DEPLOY_KEY') ?: '';
if (!$DEPLOY_KEY && file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    if (preg_match('/^DEPLOY_KEY=(.+)$/m', $envContent, $m)) {
        $DEPLOY_KEY = trim($m[1], '"\'');
    }
}
$key = $_GET['key'] ?? '';

if (!is_string($key) || $key === '' || !hash_equals($DEPLOY_KEY, $key)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid deploy key']);
    exit;
}

$action = $_GET['action'] ?? 'setup';

function logMsg($msg) {
    return $msg . "\n";
}

// --- action: check ---
if ($action === 'check') {
    if (!file_exists($root . '/vendor/autoload.php')) {
        echo json_encode([
            'status' => 'error',
            'action' => 'check',
            'error' => 'vendor/ not extracted yet. Visit ?action=setup first.',
            'vendor_zip_exists' => file_exists($root . '/vendor.zip'),
        ], JSON_PRETTY_PRINT);
        exit;
    }

    $envContent = file_exists($envFile) ? file_get_contents($envFile) : '';
    preg_match('/APP_KEY=(.+)/', $envContent, $keyMatch);

    echo json_encode([
        'status' => 'ok',
        'php' => PHP_VERSION,
        'env_exists' => file_exists($envFile),
        'app_key_set' => !empty($keyMatch),
        'vendor_exists' => is_dir($root . '/vendor'),
        'storage_writable' => is_writable($root . '/storage'),
    ], JSON_PRETTY_PRINT);
    exit;
}

// --- action: setup (extract + migrate + cleanup) ---
if ($action === 'setup') {
    $output = '';
    $errors = [];

    // Step 0: Create .env from env.production (both in htdocs/)
    $envProdFile = $root . '/env.production';
    if (file_exists($envProdFile) && !file_exists($envFile)) {
        $output .= logMsg("Creating .env from env.production...");
        if (copy($envProdFile, $envFile)) {
            $output .= logMsg(".env created");
            @unlink($envProdFile);
            $output .= logMsg("env.production removed");
        } else {
            $errors[] = 'Failed to copy env.production to .env';
            $output .= logMsg("ERROR: Failed to create .env");
        }
    } elseif (file_exists($envFile)) {
        $output .= logMsg(".env already exists");
    } else {
        $errors[] = 'No .env or env.production found';
        $output .= logMsg("ERROR: No .env file found");
    }

    // Step 1: Assemble vendor.zip from parts (if split)
    $zipFile = $root . '/vendor.zip';
    $zipParts = [
        $root . '/vendor_part_aa',
        $root . '/vendor_part_ab',
        $root . '/vendor_part_ac',
        $root . '/vendor_part_ad',
    ];

    // Check if parts exist and need to be assembled
    $partsExist = false;
    foreach ($zipParts as $part) {
        if (file_exists($part)) {
            $partsExist = true;
            break;
        }
    }

    if ($partsExist && !file_exists($zipFile)) {
        $output .= logMsg("Assembling vendor.zip from parts...");
        $fp = fopen($zipFile, 'wb');
        if ($fp) {
            foreach ($zipParts as $part) {
                if (file_exists($part)) {
                    $content = file_get_contents($part);
                    fwrite($fp, $content);
                    @unlink($part);
                }
            }
            fclose($fp);
            $output .= logMsg("vendor.zip assembled (" . filesize($zipFile) . " bytes)");
        } else {
            $errors[] = 'Failed to create vendor.zip';
            $output .= logMsg("ERROR: Cannot open vendor.zip for writing");
        }
    } elseif (!file_exists($zipFile)) {
        $output .= logMsg("vendor.zip not found, skipping extraction");
        if (!is_dir($root . '/vendor') || !file_exists($root . '/vendor/autoload.php')) {
            $errors[] = 'No vendor.zip and vendor/ not found';
        }
    }

    // Step 2: Extract vendor.zip
    $vendorDir = $root . '/vendor';
    if (file_exists($zipFile)) {
        if (!class_exists('ZipArchive')) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'action' => 'setup',
                'error' => 'PHP ZipArchive extension not available',
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Remove existing vendor/ if present
        if (is_dir($vendorDir)) {
            $output .= logMsg("Removing existing vendor/...");
            removeDir($vendorDir);
        }

        $output .= logMsg("Extracting vendor.zip...");
        $zip = new ZipArchive();
        if ($zip->open($zipFile) === true) {
            $zip->extractTo($root);
            $zip->close();
            $output .= logMsg("vendor/ extracted successfully (" . countDir($vendorDir) . " files)");
            @unlink($zipFile);
            $output .= logMsg("vendor.zip deleted");
        } else {
            $errors[] = 'Failed to open vendor.zip';
            $output .= logMsg("ERROR: Failed to extract vendor.zip");
        }
    }

    // Step 3: Clear bootstrap cache
    $cacheDir = $root . '/bootstrap/cache';
    if (is_dir($cacheDir)) {
        foreach (glob($cacheDir . '/*.php') as $cacheFile) {
            @unlink($cacheFile);
        }
        $output .= logMsg("bootstrap/cache cleared");
    }

    // Step 4: Clear stale config caches
    $frameworkCache = $root . '/storage/framework/cache';
    if (is_dir($frameworkCache)) {
        foreach (glob($frameworkCache . '/*.php') as $f) {
            @unlink($f);
        }
        foreach (glob($frameworkCache . '/*/*.php') as $f) {
            if (basename(dirname($f)) !== 'data') {
                @unlink($f);
            }
        }
        $output .= logMsg("storage/framework/cache cleared");
    }

    // Step 5: Clear stale views
    $viewsDir = $root . '/storage/framework/views';
    if (is_dir($viewsDir)) {
        foreach (glob($viewsDir . '/*.php') as $f) {
            @unlink($f);
        }
        $output .= logMsg("storage/framework/views cleared");
    }

    // Step 6: Ensure storage directories exist
    foreach (['framework/sessions', 'framework/views', 'framework/cache', 'logs', 'app/public'] as $dir) {
        $path = $root . '/storage/' . $dir;
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
    $output .= logMsg("storage directories ensured");

    // Step 7: Run migrations
    if (empty($errors) && file_exists($root . '/vendor/autoload.php')) {
        $output .= logMsg("\n=== Running migrations ===");
        try {
            require_once $root . '/vendor/autoload.php';

            if (file_exists($envFile)) {
                $dotenv = Dotenv\Dotenv::createImmutable($root);
                $dotenv->safeLoad();
            }

            $app = require_once $root . '/bootstrap/app.php';
            $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
            $kernel->call('migrate', ['--force' => true]);
            $output .= $kernel->output();
            $output .= logMsg("Migrations completed");
        } catch (\Throwable $e) {
            $errors[] = 'Migration error: ' . $e->getMessage();
            $output .= logMsg("MIGRATION ERROR: " . $e->getMessage());
        }
    } elseif (!empty($errors)) {
        $output .= logMsg("Skipping migrations due to errors");
    }

    // Step 8: Delete this script
    $self = __FILE__;
    $output .= logMsg("\nCleaning up deploy-setup.php...");

    $response = [
        'status' => empty($errors) ? 'success' : 'partial',
        'action' => 'setup',
        'output' => $output,
    ];
    if (!empty($errors)) {
        $response['errors'] = $errors;
    }

    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    // Self-delete after output
    @unlink($self);
    exit;
}

// --- action: migrate / fresh / seed (standalone) ---
if (!file_exists($root . '/vendor/autoload.php')) {
    echo json_encode([
        'status' => 'error',
        'action' => $action,
        'error' => 'vendor/ not found. Visit ?action=setup first.',
    ], JSON_PRETTY_PRINT);
    exit;
}

try {
    require_once $root . '/vendor/autoload.php';

    if (file_exists($envFile)) {
        $dotenv = Dotenv\Dotenv::createImmutable($root);
        $dotenv->safeLoad();
    }

    $app = require_once $root . '/bootstrap/app.php';
    $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

    $output = '';
    $status = 'success';

    try {
        switch ($action) {
            case 'fresh':
                $kernel->call('migrate:fresh', ['--force' => true]);
                $output .= "=== migrate:fresh ===\n" . $kernel->output();
                $kernel->call('db:seed', ['--force' => true]);
                $output .= "\n=== db:seed ===\n" . $kernel->output();
                break;
            case 'seed':
                $kernel->call('migrate', ['--force' => true]);
                $output .= "=== migrate ===\n" . $kernel->output();
                $kernel->call('db:seed', ['--force' => true]);
                $output .= "\n=== db:seed ===\n" . $kernel->output();
                break;
            case 'migrate':
            default:
                $kernel->call('migrate', ['--force' => true]);
                $output = $kernel->output();
                break;
        }
    } catch (\Throwable $e) {
        $status = 'error';
        $output = $e->getMessage() . "\n" . $e->getTraceAsString();
    }

    echo json_encode([
        'status' => $status,
        'action' => $action,
        'output' => $output,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

// --- Helpers ---
function removeDir($dir) {
    if (!is_dir($dir)) return;
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($items as $item) {
        $item->isDir() ? @rmdir($item->getRealPath()) : @unlink($item->getRealPath());
    }
    @rmdir($dir);
}

function countDir($dir) {
    $count = 0;
    if (!is_dir($dir)) return 0;
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($items as $item) {
        if ($item->isFile()) $count++;
    }
    return $count;
}
