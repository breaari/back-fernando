<?php
$endpoints = [
    '/properties',
    '/properties/featured',
    '/catalog/amenities',
    '/catalog/tags',
    '/catalog/provinces',
];
$base = 'http://localhost:8000';
foreach ($endpoints as $ep) {
    echo "--- $ep ---\n";
    $url = $base . $ep;
    $opts = [
        'http' => [
            'method' => 'GET',
            'timeout' => 5,
        ]
    ];
    $context = stream_context_create($opts);
    $result = @file_get_contents($url, false, $context);
    if ($result === false) {
        echo "ERROR fetching $url\n";
        if (!empty($http_response_header)) {
            echo implode("\n", $http_response_header) . "\n";
        }
    } else {
        echo $result . "\n";
    }
    echo "\n";
}
