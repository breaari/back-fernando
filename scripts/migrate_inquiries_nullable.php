<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Configurando tabla inquiries...\n";
    
    // Verificar el estado actual
    $stmt = $db->query("SHOW CREATE TABLE inquiries");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $createTable = $result['Create Table'];
    
    // Verificar si property_id ya permite NULL
    if (strpos($createTable, 'property_id` int(10) unsigned DEFAULT NULL') !== false) {
        echo "  ✓ La columna property_id ya permite valores NULL\n";
    } else {
        echo "  × La columna property_id NO permite NULL, modificando...\n";
        
        // Eliminar constraint si existe
        if (strpos($createTable, 'CONSTRAINT') !== false) {
            $sql1 = "ALTER TABLE inquiries DROP FOREIGN KEY inquiries_ibfk_1";
            $db->exec($sql1);
            echo "    - Constraint eliminado\n";
        }
        
        // Modificar columna
        $sql2 = "ALTER TABLE inquiries MODIFY COLUMN property_id INT UNSIGNED NULL";
        $db->exec($sql2);
        echo "    - Columna modificada\n";
    }
    
    // Intentar agregar la clave foránea si no existe (opcional, sin detener si falla)
    try {
        if (strpos($createTable, 'CONSTRAINT') === false) {
            // Primero arreglamos el tipo de la columna para que coincida con properties.id
            $db->exec("ALTER TABLE inquiries MODIFY COLUMN property_id INT NULL");
            
            $sql3 = "ALTER TABLE inquiries 
                     ADD CONSTRAINT inquiries_property_fk 
                     FOREIGN KEY (property_id) REFERENCES properties(id) 
                     ON DELETE SET NULL";
            $db->exec($sql3);
            echo "  ✓ Constraint de clave foránea agregado\n";
        } else {
            echo "  ✓ Ya existe un constraint de clave foránea\n";
        }
    } catch (Exception $e) {
        echo "  ⚠ No se pudo agregar constraint (no crítico): " . $e->getMessage() . "\n";
    }
    
    echo "\n✓ Configuración completada.\n";
    echo "  - property_id acepta valores NULL\n";
    echo "  - Ahora puedes guardar consultas sin propiedad asociada\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
