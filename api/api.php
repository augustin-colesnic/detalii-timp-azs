<?php
// api.php - simple GET/POST API for default message using SQLite
// GET:  /api/api.php?action=get           -> returns JSON { message, version, updatedAt }
// POST: /api/api.php?action=set           -> admin-only update. Body JSON: { message, version? }

header('Content-Type: application/json; charset=utf-8');

// Basic CORS helper - adjust in production to restrict origins
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Admin-Token');
}
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

$config = null;
// Prefer config stored in a sibling cgi-bin (common shared-host layout). Fallback to local config.php.
$configCandidates = [
    // Host-specific absolute cgi-bin (detected from your server output)
    '/home/inspicio/azstulcea.ro/cgi-bin/config.php',
    // common sibling cgi-bin inside the app folder
    __DIR__ . '/../cgi-bin/config.php',
    // local fallback
    __DIR__ . '/config.php'
];
foreach ($configCandidates as $cfgPath) {
    if (file_exists($cfgPath)) {
        $config = require $cfgPath;
        break;
    }
}
if (!$config) {
    // If config not found, we can't proceed.
    http_response_code(500);
    echo json_encode(['error' => 'config_not_found', 'checked' => $configCandidates], JSON_UNESCAPED_UNICODE);
    exit;
}

$dbPath = $config['db_path'];

function send($code, $data) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("PRAGMA busy_timeout = 5000;");
} catch (Exception $e) {
    send(500, ['error' => 'db_connect_failed', 'message' => $e->getMessage()]);
}

$action = $_GET['action'] ?? 'get';

if ($action === 'get' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT message, referinta, version, updatedAt, updatedBy FROM default_message WHERE id = 1 LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) send(404, ['error' => 'not_found']);
    
    // Transform keys for the client
    $payload = [
        'mesaj' => $row['message'],
        'referinta' => $row['referinta'] ?? '',
        'version' => $row['version'],
        'updatedAt' => $row['updatedAt']
    ];
    send(200, $payload);
}

if ($action === 'set' && in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT'])) {
    // admin authentication
    $provided = $_SERVER['HTTP_X_ADMIN_TOKEN'] ?? null;
    $raw = file_get_contents('php://input');
    $body = json_decode($raw, true) ?: [];
    if (!$provided && isset($body['admin_token'])) $provided = $body['admin_token'];

    if (!$provided || $provided !== $config['admin_token']) {
        send(401, ['error' => 'unauthorized']);
    }

    $mesaj = $body['mesaj'] ?? ($body['message'] ?? null);
    $referinta = $body['referinta'] ?? '';

    if (!is_string($mesaj)) send(400, ['error' => 'invalid_mesaj']);
    $mesaj = trim($mesaj);
    if ($mesaj === '') send(400, ['error' => 'empty_mesaj']);
    if (mb_strlen($mesaj) > 2000) send(400, ['error' => 'mesaj_too_long']);

    $clientVersion = isset($body['version']) ? intval($body['version']) : null;

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->query("SELECT version FROM default_message WHERE id = 1 LIMIT 1");
        $current = $stmt->fetch(PDO::FETCH_ASSOC);
        $curVer = $current ? intval($current['version']) : 0;
        if ($clientVersion !== null && $clientVersion !== $curVer) {
            $pdo->rollBack();
            send(409, ['error' => 'version_conflict', 'currentVersion' => $curVer]);
        }
        $nextVer = $curVer + 1;
        $upd = $pdo->prepare("INSERT OR REPLACE INTO default_message (id, message, referinta, version, updatedAt, updatedBy) VALUES (1, :msg, :ref, :ver, datetime('now'), :by)");
        $upd->execute([':msg' => $mesaj, ':ref' => $referinta, ':ver' => $nextVer, ':by' => 'admin']);
        $pdo->commit();

        send(200, ['mesaj' => $mesaj, 'referinta' => $referinta, 'version' => $nextVer, 'updatedAt' => date(DATE_ATOM)]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        send(500, ['error' => 'db_error', 'message' => $e->getMessage()]);
    }
}

send(400, ['error' => 'invalid_action']);
