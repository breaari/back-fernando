<?php
// Quick test of catalog endpoints
require_once __DIR__ . '/../config/config.php';

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$endpoints = [
    '/catalog/provinces',
    '/catalog/property-types',
    '/catalog/operation-types',
    '/catalog/amenities',
    '/catalog/tags'
];

foreach($endpoints as $ep) {
    curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000' . $ep);
    $response = curl_exec($ch);
    echo "\n=== " . $ep . " ===\n";
    echo $response . "\n";
}
curl_close($ch);
