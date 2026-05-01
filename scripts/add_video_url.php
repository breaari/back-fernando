<?php

try {
    $db = new PDO(
        'mysql:host=localhost;dbname=fernando_inmobiliaria;charset=utf8mb4',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $db->exec('ALTER TABLE properties ADD COLUMN video_url VARCHAR(512) NULL');
    echo "OK\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
