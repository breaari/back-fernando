<?php
require __DIR__ . '/vendor/autoload.php';

use App\Core\Database;
use App\Core\Response;

try {
    echo "Testing database connection...\n";
    $db = Database::getInstance()->getConnection();
    echo "✓ Database connected\n\n";
    
    echo "Testing property_types table...\n";
    $stmt = $db->prepare('SELECT * FROM property_types WHERE active = 1');
    $stmt->execute();
    $types = $stmt->fetchAll();
    echo "✓ Found " . count($types) . " property types\n";
    print_r($types);
    
    echo "\nTesting operation_types table...\n";
    $stmt = $db->prepare('SELECT * FROM operation_types WHERE active = 1');
    $stmt->execute();
    $ops = $stmt->fetchAll();
    echo "✓ Found " . count($ops) . " operation types\n";
    print_r($ops);
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
