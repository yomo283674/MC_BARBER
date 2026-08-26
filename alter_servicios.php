<?php
require_once __DIR__ . '/config/database.php';

try {
    $conn->query("ALTER TABLE servicios ADD COLUMN imagen VARCHAR(255) DEFAULT NULL;");
    echo "Columna 'imagen' agregada con éxito.\n";
} catch (Exception $e) {
    echo "Error agregando 'imagen': " . $e->getMessage() . "\n";
}

try {
    $conn->query("ALTER TABLE servicios ADD COLUMN id_barbero INT NULL;");
    echo "Columna 'id_barbero' agregada con éxito.\n";
} catch (Exception $e) {
    echo "Error agregando 'id_barbero': " . $e->getMessage() . "\n";
}

try {
    $conn->query("ALTER TABLE servicios ADD FOREIGN KEY (id_barbero) REFERENCES usuarios(id_usuario);");
    echo "Foreign key para 'id_barbero' agregada con éxito.\n";
} catch (Exception $e) {
    echo "Error agregando foreign key: " . $e->getMessage() . "\n";
}
