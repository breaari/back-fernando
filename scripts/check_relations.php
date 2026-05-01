<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=fernando_inmobiliaria;charset=utf8mb4', 'root', '');
    
    echo "=== MARKET STATUSES TABLE ===\n";
    $stmt = $db->query('DESCRIBE property_market_statuses');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
    
    echo "\n=== MARKET STATUSES DATA ===\n";
    $stmt = $db->query('SELECT * FROM property_market_statuses');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['id'] . ' - ' . $row['name'] . "\n";
    }
    
    echo "\n=== PROPERTY AMENITIES TABLE ===\n";
    $stmt = $db->query('DESCRIBE property_amenities');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
    
    echo "\n=== PROPERTY TAGS TABLE ===\n";
    $stmt = $db->query('DESCRIBE property_tags');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
    
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
