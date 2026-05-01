<?php

try {
    $db = new PDO(
        'mysql:host=localhost;dbname=fernando_inmobiliaria;charset=utf8mb4',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "=== PROPERTIES TABLE SCHEMA ===\n";
    $stmt = $db->query('DESCRIBE properties');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
    
    echo "\n=== LOCATIONS TABLE SCHEMA ===\n";
    $stmt = $db->query('DESCRIBE locations');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
    
    echo "\n=== SAMPLE LOCATION DATA ===\n";
    $stmt = $db->query('SELECT * FROM locations LIMIT 3');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
