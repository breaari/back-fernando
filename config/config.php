<?php

$envPath = __DIR__ . '/../.env';
if (!file_exists($envPath)) {
    throw new Exception('.env file not found');
}

if (!function_exists('parseEnv')) {
    function parseEnv(string $content): array
    {
        $vars = [];
        foreach (preg_split('/\r?\n/', $content) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;
            if (!str_contains($line, '=')) continue;

            [$key, $value] = explode('=', $line, 2);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            $vars[trim($key)] = $value;
        }
        return $vars;
    }
}

$envVars = parseEnv(file_get_contents($envPath));

return [
    'app' => [
        'env'   => $envVars['APP_ENV'] ?? 'production',
        'debug' => filter_var($envVars['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'url'   => $envVars['APP_URL'] ?? 'http://localhost',
    ],

    'database' => [
        'host'     => $envVars['DB_HOST'] ?? 'localhost',
        'port'     => (int) ($envVars['DB_PORT'] ?? 3306),
        'name'     => $envVars['DB_NAME'] ?? '',
        'user'     => $envVars['DB_USER'] ?? '',
        'password' => $envVars['DB_PASSWORD'] ?? '',
        'charset'  => 'utf8mb4',
    ],

    'jwt' => [
        'secret'     => $envVars['JWT_SECRET'] ?? '',
        'expiration' => (int) ($envVars['JWT_EXPIRATION'] ?? 86400),
    ],

    'cors' => [
        'origins' => array_values(array_filter(
            array_map('trim', explode(',', $envVars['CORS_ORIGIN'] ?? ''))
        )),
        'methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
    ],
];
