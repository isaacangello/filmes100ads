<?php
/**
 * filmes100ads — Server Reset
 *
 * Clears ALL Laravel caches on the server: bootstrap/cache, storage/framework/cache,
 * storage/framework/views. Removes stale files. Self-deletes after execution.
 *
 * Usage: ?key=YOUR_KEY
 */

header('Content-Type: application/json');

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

$removed = [];
$errors = [];

// 1. Clear bootstrap/cache (ALL .php files)
$cacheDir = $root . '/bootstrap/cache';
if (is_dir($cacheDir)) {
    foreach (glob($cacheDir . '/*.php') as $file) {
        $basename = basename($file);
        if (@unlink($file)) {
            $removed[] = "bootstrap/cache/{$basename}";
        } else {
            $errors[] = "Failed to delete bootstrap/cache/{$basename}";
        }
    }
}

// 2. Clear storage/framework/cache (all .php, except data/ subdirectory)
$frameworkCache = $root . '/storage/framework/cache';
if (is_dir($frameworkCache)) {
    foreach (glob($frameworkCache . '/*.php') as $f) {
        $rel = str_replace($root . '/', '', $f);
        if (@unlink($f)) {
            $removed[] = $rel;
        } else {
            $errors[] = "Failed to delete {$rel}";
        }
    }
    foreach (glob($frameworkCache . '/*/*.php') as $f) {
        if (basename(dirname($f)) !== 'data') {
            $rel = str_replace($root . '/', '', $f);
            if (@unlink($f)) {
                $removed[] = $rel;
            } else {
                $errors[] = "Failed to delete {$rel}";
            }
        }
    }
}

// 3. Clear storage/framework/views
$viewsDir = $root . '/storage/framework/views';
if (is_dir($viewsDir)) {
    foreach (glob($viewsDir . '/*.php') as $f) {
        $rel = str_replace($root . '/', '', $f);
        if (@unlink($f)) {
            $removed[] = $rel;
        } else {
            $errors[] = "Failed to delete {$rel}";
        }
    }
}

// 4. Remove known stale files
$staleFiles = [
    $root . '/public/.env',
];
foreach ($staleFiles as $staleFile) {
    if (file_exists($staleFile)) {
        $rel = str_replace($root . '/', '', $staleFile);
        if (@unlink($staleFile)) {
            $removed[] = $rel;
        } else {
            $errors[] = "Failed to delete {$rel}";
        }
    }
}

// 5. Ensure storage directories exist
$dirs = ['framework/sessions', 'framework/views', 'framework/cache', 'logs', 'app/public'];
$created = [];
foreach ($dirs as $dir) {
    $path = $root . '/storage/' . $dir;
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
        $created[] = $dir;
    }
}

$response = [
    'status' => empty($errors) ? 'success' : 'partial',
    'removed' => $removed,
    'created_dirs' => $created,
    'total_removed' => count($removed),
];
if (!empty($errors)) {
    $response['errors'] = $errors;
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

// Self-delete
@unlink(__FILE__);
