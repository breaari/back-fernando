<?php
// Test Router para verificar que está matcheando correctamente
require_once __DIR__ . '/../vendor/autoload.php';

$router = new \App\Core\Router();

// Simular una ruta DELETE
$_SERVER['REQUEST_METHOD'] = 'DELETE';
$_SERVER['REQUEST_URI'] = '/properties/1/images/2';

// Verificar si matchea
$path = '/properties/1/images/2';
$pattern = '^/properties/(?P<id>[^/]+)/images/(?P<imageId>[^/]+)$';

if (preg_match('#' . $pattern . '#', $path, $matches)) {
    echo "MATCH! Parámetros:\n";
    print_r(array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY));
} else {
    echo "NO MATCH\n";
}
