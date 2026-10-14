<?php
require_once __DIR__ . '/ListaDoblementeEnlazada.php';

function cargarListaDesdeMySQL(mysqli $conexion, string $consultaSql): ListaDoblementeEnlazada
{
    $lista = new ListaDoblementeEnlazada();
    $resultado = $conexion->query($consultaSql);
    while ($fila = $resultado->fetch_assoc()) {
        $lista->insertarAlFinal($fila);
    }
    $resultado->free();
    return $lista;
}
