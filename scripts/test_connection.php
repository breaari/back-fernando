<?php

require_once __DIR__ . '/../vendor/autoload.php';

try {
    $config = require __DIR__ . '/../config/config.php';
    print_r($config);

    $db = $config['database'];
    $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset={$db['charset']}";
    $pdo = new PDO($dsn, $db['user'], $db['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    echo "DB connection OK\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
