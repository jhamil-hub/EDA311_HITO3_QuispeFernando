<?php
require_once __DIR__ . '/includes/crud.php';
$tituloPagina = 'Inicio';
include __DIR__ . '/includes/cabecera.php';
?>
<h1>Gestion de la tienda</h1>
<p class="info">Aplicacion CRUD con PHP + MySQL, lista doblemente enlazada y ordenamiento burbuja.</p>
<div class="menu">
  <a href="gestionar.php?tabla=clientes">Clientes</a>
  <a href="gestionar.php?tabla=pedidos">Pedidos</a>
  <a href="gestionar.php?tabla=productos">Productos</a>
</div>
<?php include __DIR__ . '/includes/pie.php'; ?>
