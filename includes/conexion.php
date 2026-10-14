<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function obtenerConexion(): mysqli
{
    $rutaConfig = __DIR__ . '/../config.php';
    if (!file_exists($rutaConfig)) {
        die('Falta config.php. Copia config.example.php como config.php y completa tus datos.');
    }
    $c = require $rutaConfig;
    $conexion = new mysqli($c['host'], $c['usuario'], $c['password'], $c['base'], (int)$c['puerto']);
    $conexion->set_charset('utf8mb4');
    return $conexion;
}
