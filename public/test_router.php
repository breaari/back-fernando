<?php

// Test del patrón regex
$path = '/properties/{id}/images/{imageId}';
$currentPath = '/properties/1/images/2';

// Convertir path a regex
$pattern = preg_replace_callback('/{([a-zA-Z0-9_]+)}/', function ($matches) {
    return '(?P<' . $matches[1] . '>[^/]+)';
}, $path);
$pattern = '^' . $pattern . '$';

echo "Original path: $path\n";
echo "Current path: $currentPath\n";
echo "Regex pattern: $pattern\n";

if (preg_match('#' . $pattern . '#', $currentPath, $matches)) {
    echo "MATCH!\n";
    print_r($matches);
    
    // Extraer parámetros
    $paramNames = [];
    preg_replace_callback('/{([a-zA-Z0-9_]+)}/', function ($m) use (&$paramNames) {
        $paramNames[] = $m[1];
        return $m[0];
    }, $path);
    
    echo "Parameter names: " . json_encode($paramNames) . "\n";
    
    $params = [];
    foreach ($paramNames as $paramName) {
        $params[] = $matches[$paramName] ?? null;
    }
    echo "Final params: " . json_encode($params) . "\n";
} else {
    echo "NO MATCH\n";
}
