<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\{Router, Request, Response};

// Cargar configuración para CORS
$config = require __DIR__ . '/../config/config.php';
$origins = $config['cors']['origins'] ?? [];
$requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Determinar el origen permitido de forma segura
$allowOrigin = '';
if ($requestOrigin && in_array($requestOrigin, $origins, true)) {
    // Solo permitir orígenes específicamente listados
    $allowOrigin = $requestOrigin;
} elseif (empty($origins)) {
    // Si no hay orígenes configurados, denegar acceso
    http_response_code(403);
    echo json_encode(['error' => 'CORS not configured']);
    exit;
}

// Configurar headers CORS solo si hay un origen válido
if ($allowOrigin) {
    header('Access-Control-Allow-Origin: ' . $allowOrigin);
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
}
header('Content-Type: application/json; charset=utf-8');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Crear router
    $router = new Router();

    // ==================== AUTH ROUTES ====================
    $router->post('/auth/register', 'AuthController@register');
    $router->post('/auth/login', 'AuthController@login');
    $router->get('/auth/me', 'AuthController@me');
    $router->post('/auth/logout', 'AuthController@logout');

    // ==================== PROPERTY ROUTES ====================
    $router->get('/properties', 'PropertyController@index');
    $router->get('/properties/featured', 'PropertyController@featured');
    $router->get('/properties/my-properties', 'PropertyController@myProperties');
    $router->get('/properties/search', 'PropertyController@search');
    $router->get('/properties/{id}', 'PropertyController@show');
    $router->post('/properties', 'PropertyController@create');
    $router->put('/properties/{id}', 'PropertyController@update');
    $router->delete('/properties/{id}', 'PropertyController@delete');

    // Property Amenities
    $router->post('/properties/{id}/amenities', 'PropertyController@addAmenity');
    $router->delete('/properties/{id}/amenities', 'PropertyController@removeAmenity');

    // Property Tags
    $router->post('/properties/{id}/tags', 'PropertyController@addTag');
    $router->delete('/properties/{id}/tags', 'PropertyController@removeTag');

    // ==================== PROPERTY IMAGE ROUTES ====================
    $router->post('/properties/{id}/images', 'PropertyImageController@upload');
    $router->put('/properties/{id}/images/{imageId}/cover', 'PropertyImageController@setCover');
    $router->delete('/properties/{id}/images/{imageId}', 'PropertyImageController@delete');
    $router->post('/properties/{id}/images/reorder', 'PropertyImageController@reorder');

    // ==================== INQUIRY ROUTES ====================
    $router->get('/inquiries', 'InquiryController@getAll');
    $router->post('/inquiries', 'InquiryController@create');
    $router->get('/inquiries/{id}', 'InquiryController@show');
    $router->get('/properties/{id}/inquiries', 'InquiryController@getByProperty');
    $router->delete('/inquiries/{id}', 'InquiryController@delete');

    // ==================== CONTACT ROUTES ====================
    $router->post('/contact', 'ContactController@create');

    // ==================== CATALOG ROUTES ====================
    $router->get('/catalog/amenities', 'CatalogController@getAmenities');
    $router->get('/catalog/tags', 'CatalogController@getTags');
    $router->get('/catalog/provinces', 'CatalogController@getProvinces');
    $router->get('/catalog/provinces/{province}/cities', 'CatalogController@getCities');
    $router->get('/catalog/cities/{city}/neighborhoods', 'CatalogController@getNeighborhoods');
    $router->get('/catalog/property-types', 'CatalogController@getPropertyTypes');
    $router->get('/catalog/operation-types', 'CatalogController@getOperationTypes');
    $router->get('/catalog/market-statuses', 'CatalogController@getMarketStatuses');

    // Dispatch the request
    $router->dispatch();
} catch (Exception $e) {
    Response::error('Server Error: ' . $e->getMessage(), 500);
}
