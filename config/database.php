<?php
$host = '127.0.0.1';
$user = 'root';
$password = '';
$db = 'mc_barberdb';
$port = 3306;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $password, $db, $port);
    
    // Cargar configuracion global para usar en vistas y politicas
    $globalConfig = null;
    $stmtConfig = $conn->query("SELECT * FROM configuracion_sistema LIMIT 1");
    if ($stmtConfig && $stmtConfig->num_rows > 0) {
        $globalConfig = $stmtConfig->fetch_assoc();
    }
    
    if (empty($globalConfig)) {
        $globalConfig = [
            'nombre_negocio' => 'MC BARBER',
            'logo_url' => '',
            'horario_apertura' => '08:00:00',
            'horario_cierre' => '20:00:00',
            'duracion_cita_min' => 30,
            'tiempo_cancelacion_min' => 10,
            'tiempo_reprogramacion_min' => 10,
            'tiempo_notificacion_min' => 2,
        ];
    }
    
    // Fallback de logo si está vacío
    if (empty($globalConfig['logo_url'])) {
        $globalConfig['logo_url'] = 'public/img/logo_corona.jpg';
    }
} catch (mysqli_sql_exception $e) {
    die('Error de Conexion: ' . $e->getMessage());
}
?>