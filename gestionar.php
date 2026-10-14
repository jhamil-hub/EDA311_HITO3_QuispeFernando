<?php
require_once __DIR__ . '/includes/conexion.php';
require_once __DIR__ . '/includes/crud.php';
require_once __DIR__ . '/src/CargadorLista.php';

$definiciones = require __DIR__ . '/includes/tablas.php';
$tabla = $_GET['tabla'] ?? 'clientes';
if (!isset($definiciones[$tabla])) { http_response_code(404); die('Tabla no valida.'); }
$def = $definiciones[$tabla];
$pk = $def['pk'];

$db = obtenerConexion();
$mensaje = $_GET['ok'] ?? '';
$errores = [];
$registroEditar = null;
$datosFormulario = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    try {
        if ($accion === 'eliminar') {
            eliminar($db, $tabla, $def, $id);
            header("Location: gestionar.php?tabla=$tabla&ok=" . urlencode('Registro eliminado correctamente.')); exit;
        }
        [$datosFormulario, $errores] = validarFormulario($def, $_POST);
        if (!$errores) {
            if ($accion === 'crear') {
                registrar($db, $tabla, $def, $datosFormulario);
                header("Location: gestionar.php?tabla=$tabla&ok=" . urlencode('Registro creado correctamente.')); exit;
            }
            if ($accion === 'modificar') {
                modificar($db, $tabla, $def, $id, $datosFormulario);
                header("Location: gestionar.php?tabla=$tabla&ok=" . urlencode("Registro #$id modificado correctamente.")); exit;
            }
        } elseif ($accion === 'modificar') {
            $registroEditar = [$pk => $id] + $datosFormulario;
        }
    } catch (mysqli_sql_exception $e) {
        $errores[] = mensajeError($e);
        if ($accion === 'modificar') $registroEditar = [$pk => $id] + $datosFormulario;
    }
}

if (isset($_GET['editar']) && !$registroEditar) {
    $registroEditar = obtenerPorId($db, $tabla, $def, (int)$_GET['editar']);
}
$editando = $registroEditar !== null;

$lista = cargarListaDesdeMySQL($db, $def['consulta']);

$direccion = ($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
$listaOrdenada = cargarListaDesdeMySQL($db, $def['consulta']);
$listaOrdenada->ordenarBurbuja($def['orden']['campo'], $def['orden']['numerico'], $direccion === 'asc');

$columnas = $def['columnas'] ?? array_merge([$pk => 'ID'], array_map(fn($c) => $c['label'], $def['campos']));
$clientes = $tabla === 'pedidos' ? $db->query('SELECT id_cliente, nombre FROM clientes ORDER BY nombre')->fetch_all(MYSQLI_ASSOC) : [];

function valorForm(string $campo, ?array $registro)
{
    return $registro[$campo] ?? '';
}

function dibujarTabla(ListaDoblementeEnlazada $lista, array $columnas, string $tabla, string $pk, bool $acciones): void
{
    echo '<table><thead><tr>';
    foreach ($columnas as $titulo) echo '<th>' . h($titulo) . '</th>';
    if ($acciones) echo '<th>Acciones</th>';
    echo '</tr></thead><tbody>';
    if ($lista->tamano() === 0) {
        echo '<tr><td colspan="' . (count($columnas) + 1) . '">No hay registros.</td></tr>';
    }
    foreach ($lista->recorrer() as $nodo) {
        echo '<tr>';
        foreach ($columnas as $campo => $_) echo '<td>' . h($nodo->datos[$campo] ?? '') . '</td>';
        if ($acciones) {
            $id = (int)$nodo->datos[$pk];
            echo '<td class="acciones"><a class="boton" href="gestionar.php?tabla=' . $tabla . '&editar=' . $id . '#formulario">Editar</a> '
               . '<form method="post" onsubmit="return confirm(\'¿Eliminar el registro #' . $id . '?\');">'
               . '<input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="' . $id . '">'
               . '<button class="peligro">Eliminar</button></form></td>';
        }
        echo '</tr>';
    }
    echo '</tbody></table>';
}

$tituloPagina = $def['titulo'];
$paginaActual = $tabla;
include __DIR__ . '/includes/cabecera.php';
?>
<h1><?= h($def['titulo']) ?></h1>

<?php if ($mensaje): ?><div class="exito"><?= h($mensaje) ?></div><?php endif; ?>
<?php foreach ($errores as $e): ?><div class="error"><?= h($e) ?></div><?php endforeach; ?>

<div class="tarjeta" id="formulario">
  <h2><?= $editando ? 'Modificar registro #' . (int)$registroEditar[$pk] : 'Registrar nuevo' ?></h2>
  <form method="post" class="campos" action="gestionar.php?tabla=<?= h($tabla) ?>">
    <input type="hidden" name="accion" value="<?= $editando ? 'modificar' : 'crear' ?>">
    <?php if ($editando): ?><input type="hidden" name="id" value="<?= (int)$registroEditar[$pk] ?>"><?php endif; ?>
    <?php foreach ($def['campos'] as $nombre => $c):
        $valor = $editando ? valorForm($nombre, $registroEditar) : ($datosFormulario[$nombre] ?? ''); ?>
      <label><?= h($c['label']) ?>
      <?php if ($c['tipo'] === 'cliente'): ?>
        <select name="<?= $nombre ?>" required>
          <option value="">-- Seleccione --</option>
          <?php foreach ($clientes as $cl): ?>
            <option value="<?= (int)$cl['id_cliente'] ?>" <?= (string)$valor === (string)$cl['id_cliente'] ? 'selected' : '' ?>>
              <?= h($cl['nombre']) ?> (#<?= (int)$cl['id_cliente'] ?>)</option>
          <?php endforeach; ?>
        </select>
      <?php else:
          $type = match ($c['tipo']) { 'email' => 'email', 'entero' => 'number', 'decimal' => 'number', 'fecha' => 'date', default => 'text' };
          $extra = $c['tipo'] === 'decimal' ? 'step="0.01"' : ($c['tipo'] === 'entero' ? 'step="1"' : ''); ?>
        <input type="<?= $type ?>" name="<?= $nombre ?>" value="<?= h($valor) ?>" <?= $extra ?>
               <?= isset($c['max']) && $type === 'text' ? 'maxlength="' . $c['max'] . '"' : '' ?>
               <?= isset($c['min']) ? 'min="' . $c['min'] . '"' : '' ?> required>
      <?php endif; ?>
      </label>
    <?php endforeach; ?>
    <div class="fila-botones">
      <button><?= $editando ? 'Guardar cambios' : 'Registrar' ?></button>
      <?php if ($editando): ?><a class="boton gris" href="gestionar.php?tabla=<?= h($tabla) ?>">Cancelar</a><?php endif; ?>
    </div>
  </form>
</div>

<div class="tarjeta">
  <h2>Registros recuperados desde MySQL (lista doblemente enlazada, recorrido normal)</h2>
  <p class="info">Total de nodos: <?= $lista->tamano() ?>. Orden original por <?= h($pk) ?>.</p>
  <?php dibujarTabla($lista, $columnas, $tabla, $pk, true); ?>
</div>

<div class="tarjeta">
  <h2>Lista ordenada con Bubble Sort</h2>
  <p class="info">
    Criterio: <strong><?= h($def['orden']['texto']) ?></strong>
    (<?= $direccion === 'asc' ? 'ascendente' : 'descendente' ?>) &nbsp;|&nbsp;
    <a href="gestionar.php?tabla=<?= h($tabla) ?>&dir=asc">Ascendente</a> ·
    <a href="gestionar.php?tabla=<?= h($tabla) ?>&dir=desc">Descendente</a><br>
    Pasadas: <?= $listaOrdenada->pasadas ?> · Comparaciones: <?= $listaOrdenada->comparaciones ?> ·
    Intercambios: <?= $listaOrdenada->intercambios ?>
  </p>
  <?php dibujarTabla($listaOrdenada, $columnas, $tabla, $pk, false); ?>
</div>
<?php include __DIR__ . '/includes/pie.php'; ?>
