<?php
/**
 * cpanel-deploy.php - Webhook Auto-Deployment Laravel 11
 * Menerima payload push dari GitHub Webhook dan mengeksekusi pull + deploy.
 *
 * Token TIDAK boleh ditulis di file ini: file ini ikut ter-commit ke GitHub,
 * dan repo ini PUBLIC - siapa pun bisa membacanya lalu memicu deploy.
 * Token dibaca dari DEPLOY_WEBHOOK_TOKEN di file .env (tidak ikut ke git).
 */

header('Content-Type: application/json');

$secretToken = '';
$envFile = dirname(__DIR__).'/.env';
if (is_readable($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES) as $line) {
        if (preg_match('/^DEPLOY_WEBHOOK_TOKEN=(.*)$/', trim($line), $m)) {
            $secretToken = trim($m[1], " \t\n\r\0\x0B\"'");
            break;
        }
    }
}

// Fail closed: tanpa token yang terkonfigurasi, endpoint ini tidak pernah bisa dipakai.
if ($secretToken === '' || !hash_equals($secretToken, (string) ($_GET['token'] ?? ''))) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Token tidak valid.']);
    exit;
}

$repoDir = dirname(__DIR__); // Asumsi file ini ada di public/ atau root project
if (basename($repoDir) === 'public_html' || basename($repoDir) === 'public') {
    $projectRoot = dirname($repoDir);
} else {
    $projectRoot = $repoDir;
}

$output = [];
$output['time'] = date('Y-m-d H:i:s');
$output['project_root'] = $projectRoot;

if (is_dir($projectRoot . '/.git')) {
    // 1. Git pull & reset bersih ke origin main
    $cmdPull = "cd " . escapeshellarg($projectRoot) . " && git fetch origin main 2>&1 && git reset --hard origin/main 2>&1";
    $resPull = shell_exec($cmdPull);
    $output['git_pull'] = trim((string)$resPull);

    // 2. Laravel optimize & cache update
    $cmdArtisan = "cd " . escapeshellarg($projectRoot) . " && php artisan config:cache 2>&1 && php artisan route:cache 2>&1 && php artisan view:cache 2>&1";
    $resArtisan = shell_exec($cmdArtisan);
    $output['artisan'] = trim((string)$resArtisan);

    // 3. Fix permissions file/folder
    shell_exec("find " . escapeshellarg($projectRoot . '/storage') . " -type d -exec chmod 775 {} + 2>/dev/null");
    shell_exec("find " . escapeshellarg($projectRoot . '/bootstrap/cache') . " -type d -exec chmod 775 {} + 2>/dev/null");
    $output['status'] = 'deployed';
} else {
    $output['status'] = 'error';
    $output['message'] = 'Bukan direktori git repository.';
}

echo json_encode(['status' => 'success', 'details' => $output], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
