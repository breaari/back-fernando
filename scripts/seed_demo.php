<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Core\Auth;

// Cargar config
$config = require __DIR__ . '/../config/config.php';
$dbConf = $config['database'];

$dsn = "mysql:host={$dbConf['host']};port={$dbConf['port']};dbname={$dbConf['name']};charset={$dbConf['charset']}";
$pdo = new PDO($dsn, $dbConf['user'], $dbConf['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

try {
    $pdo->beginTransaction();

    // Crear real_estate de prueba si no existe
    $stmt = $pdo->prepare('SELECT id FROM real_estates WHERE name = ? LIMIT 1');
    $stmt->execute(['Demo RealEstate']);
    $re = $stmt->fetchColumn();
    if (!$re) {
        $stmt = $pdo->prepare('INSERT INTO real_estates (name, email, phone, active) VALUES (?, ?, ?, ?)');
        $stmt->execute(['Demo RealEstate', 'demo@realestate.test', '123456789', 1]);
        $re = $pdo->lastInsertId();
    }

    // Crear ubicación de prueba
    $stmt = $pdo->prepare('SELECT id FROM locations WHERE city = ? LIMIT 1');
    $stmt->execute(['Ciudad Demo']);
    $loc = $stmt->fetchColumn();
    if (!$loc) {
        $stmt = $pdo->prepare('INSERT INTO locations (country, province, city, neighborhood) VALUES (?, ?, ?, ?)');
        $stmt->execute(['Argentina', 'Provincia Demo', 'Ciudad Demo', 'Barrio Demo']);
        $loc = $pdo->lastInsertId();
    }

    // Crear usuario demo
    $email = 'agent.demo@example.com';
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $userId = $stmt->fetchColumn();
    if (!$userId) {
        $passwordHash = password_hash('secret123', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('INSERT INTO users (real_estate_id, name, email, password, role, phone, active) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$re, 'Agent Demo', $email, $passwordHash, 'agent', '123456789', 1]);
        $userId = $pdo->lastInsertId();
    }

    // Asegurar operation_type_id y property_type_id
    $stmt = $pdo->query('SELECT id FROM operation_types LIMIT 1');
    $operationTypeId = $stmt->fetchColumn() ?: 1;
    $stmt = $pdo->query('SELECT id FROM property_types LIMIT 1');
    $propertyTypeId = $stmt->fetchColumn() ?: 1;

    // Crear propiedad de ejemplo publicada
    $title = 'Departamento demo - 2 ambientes';
    $stmt = $pdo->prepare('SELECT id FROM properties WHERE title = ? LIMIT 1');
    $stmt->execute([$title]);
    $propId = $stmt->fetchColumn();
    if (!$propId) {
        $stmt = $pdo->prepare('INSERT INTO properties (title, description, price, currency, location_id, status, operation_type_id, property_type_id, user_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
        $stmt->execute([
            $title,
            'Propiedad de demo creada por seed script',
            120000.00,
            'ARS',
            $loc,
            'published',
            $operationTypeId,
            $propertyTypeId,
            $userId
        ]);
        $propId = $pdo->lastInsertId();
    }

    // Añadir imagen de ejemplo
    $stmt = $pdo->prepare('SELECT id FROM property_images WHERE property_id = ? LIMIT 1');
    $stmt->execute([$propId]);
    $img = $stmt->fetchColumn();
    if (!$img) {
        $stmt = $pdo->prepare('INSERT INTO property_images (property_id, image_url, is_cover, position) VALUES (?, ?, ?, ?)');
        $stmt->execute([$propId, '/uploads/sample.jpg', 1, 0]);
    }

    // Añadir amenity y tag si existen
    $stmt = $pdo->prepare('SELECT id FROM amenities LIMIT 1');
    $stmt->execute();
    $amenityId = $stmt->fetchColumn();
    if ($amenityId) {
        $stmt = $pdo->prepare('INSERT IGNORE INTO property_amenities (property_id, amenity_id) VALUES (?, ?)');
        $stmt->execute([$propId, $amenityId]);
    }

    $stmt = $pdo->prepare('SELECT id FROM tags LIMIT 1');
    $stmt->execute();
    $tagId = $stmt->fetchColumn();
    if ($tagId) {
        $stmt = $pdo->prepare('INSERT IGNORE INTO property_tags (property_id, tag_id) VALUES (?, ?)');
        $stmt->execute([$propId, $tagId]);
    }

    $pdo->commit();

    echo "Seed finished. user_id=$userId property_id=$propId\n";

    // Generar token usando Auth
    $token = Auth::generateToken($userId, $email, 'agent');
    echo "Token: $token\n";

    echo "Login: email=$email password=secret123\n";
    echo "Property URL: /properties/$propId\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Seed failed: " . $e->getMessage() . "\n";
    exit(1);
}
