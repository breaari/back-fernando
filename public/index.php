<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$autoloadPath = __DIR__ . '/../vendor/autoload.php';

if (!file_exists($autoloadPath)) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'error' => 'Falta vendor/autoload.php',
        'ruta_buscada' => $autoloadPath,
    ]);
    exit;
}

require_once $autoloadPath;

use App\Core\{Router, Response};

$configPath = __DIR__ . '/../config/config.php';

if (!file_exists($configPath)) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'error' => 'Falta config/config.php',
        'ruta_buscada' => $configPath,
    ]);
    exit;
}

$config = require $configPath;

$origins = $config['cors']['origins'] ?? [];
$requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';

if ($requestOrigin && in_array($requestOrigin, $origins, true)) {
    header('Access-Control-Allow-Origin: ' . $requestOrigin);
    header('Vary: Origin');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

/*
|--------------------------------------------------------------------------
| Normalizar URL para Hostinger / subcarpeta
|--------------------------------------------------------------------------
| Convierte:
| /back-fernando/public/index.php/auth/login
| /back-fernando/public/auth/login
|
| En:
| /auth/login
|--------------------------------------------------------------------------
*/

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

$basePaths = [
    '/back-fernando/public/index.php',
    '/back-fernando/public',
];

foreach ($basePaths as $basePath) {
    if (str_starts_with($requestUri, $basePath)) {
        $requestUri = substr($requestUri, strlen($basePath));
        break;
    }
}

$requestUri = '/' . ltrim($requestUri, '/');
$requestUri = $requestUri === '/' ? '/' : rtrim($requestUri, '/');

$_SERVER['REQUEST_URI'] = $requestUri;

try {
    $router = new Router();

    // AUTH
    $router->post('/auth/register', 'AuthController@register');
    $router->post('/auth/login', 'AuthController@login');
    $router->get('/auth/me', 'AuthController@me');
    $router->post('/auth/logout', 'AuthController@logout');

    // PROPERTIES
    $router->get('/properties', 'PropertyController@index');
    $router->get('/properties/featured', 'PropertyController@featured');
    $router->get('/properties/my-properties', 'PropertyController@myProperties');
    $router->get('/properties/search', 'PropertyController@search');
    $router->get('/properties/{id}', 'PropertyController@show');
    $router->post('/properties', 'PropertyController@create');
    $router->put('/properties/{id}', 'PropertyController@update');
    $router->delete('/properties/{id}', 'PropertyController@delete');

    // PROPERTY AMENITIES
    $router->post('/properties/{id}/amenities', 'PropertyController@addAmenity');
    $router->delete('/properties/{id}/amenities', 'PropertyController@removeAmenity');

    // PROPERTY TAGS
    $router->post('/properties/{id}/tags', 'PropertyController@addTag');
    $router->delete('/properties/{id}/tags', 'PropertyController@removeTag');

    // PROPERTY IMAGES
    $router->post('/properties/{id}/images', 'PropertyImageController@upload');
    $router->put('/properties/{id}/images/{imageId}/cover', 'PropertyImageController@setCover');
    $router->delete('/properties/{id}/images/{imageId}', 'PropertyImageController@delete');
    $router->post('/properties/{id}/images/reorder', 'PropertyImageController@reorder');

    // INQUIRIES
    $router->get('/inquiries', 'InquiryController@getAll');
    $router->post('/inquiries', 'InquiryController@create');
    $router->get('/inquiries/{id}', 'InquiryController@show');
    $router->get('/properties/{id}/inquiries', 'InquiryController@getByProperty');
    $router->delete('/inquiries/{id}', 'InquiryController@delete');

    // CONTACT
    $router->post('/contact', 'ContactController@create');

    // CATALOG
    $router->get('/catalog/amenities', 'CatalogController@getAmenities');
    $router->get('/catalog/tags', 'CatalogController@getTags');
    $router->get('/catalog/provinces', 'CatalogController@getProvinces');
    $router->get('/catalog/provinces/{province}/cities', 'CatalogController@getCities');
    $router->get('/catalog/cities/{city}/neighborhoods', 'CatalogController@getNeighborhoods');
    $router->get('/catalog/property-types', 'CatalogController@getPropertyTypes');
    $router->get('/catalog/operation-types', 'CatalogController@getOperationTypes');
    $router->get('/catalog/market-statuses', 'CatalogController@getMarketStatuses');

    $router->dispatch();

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Server Error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
}