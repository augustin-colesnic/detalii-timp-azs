<?php
// init_db.php - Run once to create the SQLite DB and initial row.
// Usage: upload to server and open in browser, then remove or protect.

$config = null;
$configCandidates = [
    '/home/inspicio/azstulcea.ro/cgi-bin/config.php',
    __DIR__ . '/../cgi-bin/config.php',
    __DIR__ . '/config.php'
];
foreach ($configCandidates as $cfgPath) {
    if (file_exists($cfgPath)) {
        $config = require $cfgPath;
        break;
    }
}
if (!$config) {
    echo "Config not found. Checked: " . implode(', ', $configCandidates);
    exit;
}

$dbPath = $config['db_path'];
$dir = dirname($dbPath);
if (!is_dir($dir)) mkdir($dir, 0755, true);

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("PRAGMA journal_mode = WAL;");
    $pdo->exec("PRAGMA busy_timeout = 5000;");

    $pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS default_message (
  id INTEGER PRIMARY KEY CHECK (id = 1),
  message TEXT NOT NULL,
  version INTEGER NOT NULL DEFAULT 1,
  updatedAt TEXT NOT NULL,
  updatedBy TEXT
);
SQL
    );

    $stmt = $pdo->prepare("INSERT OR IGNORE INTO default_message (id, message, version, updatedAt, updatedBy) VALUES (1, :msg, 1, datetime('now'), 'system')");
    $stmt->execute([':msg' => 'Bun venit!']);

    echo "DB initialized at {$dbPath}\n";
} catch (Exception $e) {
    echo "Initialization failed: " . htmlspecialchars($e->getMessage());
}
