<?php $paginaActual = $paginaActual ?? ''; ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($tituloPagina ?? 'Tienda') ?> - EDA311 Hito 3</title>
<link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<nav>
  <strong>Tienda Quispe</strong>
  <a href="index.php" class="<?= $paginaActual === '' ? 'activo' : '' ?>">Inicio</a>
  <a href="gestionar.php?tabla=clientes" class="<?= $paginaActual === 'clientes' ? 'activo' : '' ?>">Clientes</a>
  <a href="gestionar.php?tabla=pedidos" class="<?= $paginaActual === 'pedidos' ? 'activo' : '' ?>">Pedidos</a>
  <a href="gestionar.php?tabla=productos" class="<?= $paginaActual === 'productos' ? 'activo' : '' ?>">Productos</a>
</nav>
<main>
