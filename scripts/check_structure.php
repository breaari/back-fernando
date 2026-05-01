<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    // Ver estructura de inquiries
    $stmt = $db->query('SHOW CREATE TABLE inquiries');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Estructura actual de inquiries:\n";
    echo $result['Create Table'] . "\n\n";
    
    // Ver estructura de properties para comparar
    $stmt2 = $db->query('DESCRIBE properties');
    $cols = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    echo "Columna id de properties:\n";
    foreach ($cols as $col) {
        if ($col['Field'] === 'id') {
            print_r($col);
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
