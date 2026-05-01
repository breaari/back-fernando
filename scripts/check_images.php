<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=fernando_inmobiliaria;charset=utf8mb4', 'root', '');
    
    echo "=== PROPERTY_IMAGES SCHEMA ===\n";
    $stmt = $db->query('DESCRIBE property_images');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
    
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
