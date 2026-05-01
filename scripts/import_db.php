<?php

// Script CLI para importar un dump SQL usando la configuración en .env
if (php_sapi_name() !== 'cli') {
    echo "Run this script from CLI: php scripts/import_db.php /path/to/dump.sql\n";
    exit(1);
}

require_once __DIR__ . '/../vendor/autoload.php';

// Cargar configuración (se encarga de leer .env)
$config = require __DIR__ . '/../config/config.php';

// Argumento: ruta al archivo SQL
$sqlPath = $argv[1] ?? __DIR__ . '/../fernando_inmobiliaria.sql';

if (!file_exists($sqlPath)) {
    echo "SQL file not found: $sqlPath\n";
    echo "Pasa la ruta al archivo como argumento, por ejemplo:\n";
    echo "  php scripts/import_db.php \"C:\\Users\\Ariana\\Downloads\\fernando_inmobiliaria.sql\"\n";
    exit(1);
}

try {
    // Construir DSN sin el nombre de la base para permitir crear DB si fuera necesario
    $dbConf = $config['database'];
    $dsnNoDb = "mysql:host={$dbConf['host']};port={$dbConf['port']};charset={$dbConf['charset']}";

    $pdo = new PDO($dsnNoDb, $dbConf['user'], $dbConf['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Crear la base de datos si no existe
    $dbName = $dbConf['name'];
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET {$dbConf['charset']} COLLATE {$dbConf['charset']}_general_ci");
    echo "Database ensured: $dbName\n";

    // Conectar ya con la DB
    $dsnWithDb = "mysql:host={$dbConf['host']};port={$dbConf['port']};dbname={$dbName};charset={$dbConf['charset']}";
    $pdo = new PDO($dsnWithDb, $dbConf['user'], $dbConf['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Leer contenido del archivo SQL
    $sql = file_get_contents($sqlPath);
    if ($sql === false) {
        throw new Exception("Cannot read SQL file");
    }

    // MySQL puede ejecutar múltiples declaraciones en exec; para mayor robustez
    // se separan statements por ";\n" cuando existan y se ejecutan uno a uno.
    $statements = preg_split('/;\s*\n/', $sql);

    $count = 0;
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if ($statement === '' || strpos($statement, '--') === 0 || strpos($statement, '/*') === 0) continue;
        try {
            $pdo->exec($statement);
            $count++;
        } catch (PDOException $e) {
            // Mostrar el error pero continuar
            echo "Statement failed: " . substr($statement, 0, 120) . "... -> " . $e->getMessage() . "\n";
        }
    }

    echo "Import finished. Executed approx $count statements.\n";
    exit(0);
} catch (Exception $e) {
    echo "Import failed: " . $e->getMessage() . "\n";
    exit(1);
}
