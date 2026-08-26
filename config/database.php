<?php
$host = '127.0.0.1';
$user = 'root';
$password = '';
$db = 'mc_barberdb';
$port = 3306;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $password, $db, $port);
} catch (mysqli_sql_exception $e) {
    die('Error de Conexion: ' . $e->getMessage());
}
?>