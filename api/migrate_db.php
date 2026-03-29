<?php
// migrate_db.php - Adds referinta column to default_message table
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
    echo "Config not found.\n";
    exit;
}

$dbPath = $config['db_path'];

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if column exists
    $stmt = $pdo->query("PRAGMA table_info(default_message)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $hasReferinta = false;
    foreach ($columns as $col) {
        if ($col['name'] === 'referinta') {
            $hasReferinta = true;
            break;
        }
    }
    
    if (!$hasReferinta) {
        $pdo->exec("ALTER TABLE default_message ADD COLUMN referinta TEXT");
        echo "Column 'referinta' added to 'default_message' table.\n";
    } else {
        echo "Column 'referinta' already exists.\n";
    }

    // Optional: Rename 'message' to 'mesaj' in the table structure if we want to be fully consistent,
    // but SQLite 'ALTER TABLE RENAME COLUMN' is only supported in newer versions (3.25.0+).
    // Let's just use aliases in the PHP code for compatibility.
    
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
