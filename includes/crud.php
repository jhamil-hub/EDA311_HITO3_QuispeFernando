<?php

function tipoBind(string $tipo): string
{
    return match ($tipo) {
        'entero', 'cliente' => 'i',
        'decimal' => 'd',
        default => 's',
    };
}
function validarFormulario(array $def, array $post): array
{
    $datos = [];
    $errores = [];
    foreach ($def['campos'] as $nombre => $c) {
        $valor = trim((string)($post[$nombre] ?? ''));
        if ($valor === '') {
            $errores[] = "El campo {$c['label']} es obligatorio.";
            continue;
        }
        switch ($c['tipo']) {
            case 'texto':
                if (mb_strlen($valor) > $c['max']) $errores[] = "{$c['label']} supera {$c['max']} caracteres.";
                break;
            case 'email':
                if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) $errores[] = 'El email no es valido.';
                elseif (mb_strlen($valor) > $c['max']) $errores[] = "{$c['label']} es demasiado largo.";
                break;
            case 'entero':
            case 'cliente':
                if (filter_var($valor, FILTER_VALIDATE_INT) === false) { $errores[] = "{$c['label']} debe ser un numero entero."; break; }
                $valor = (int)$valor;
                if (isset($c['min']) && $valor < $c['min']) $errores[] = "{$c['label']} no puede ser menor que {$c['min']}.";
                if (isset($c['max']) && $valor > $c['max']) $errores[] = "{$c['label']} no puede ser mayor que {$c['max']}.";
                break;
            case 'decimal':
                if (!is_numeric($valor)) { $errores[] = "{$c['label']} debe ser un numero."; break; }
                $valor = (float)$valor;
                if (isset($c['min']) && $valor < $c['min']) $errores[] = "{$c['label']} no puede ser menor que {$c['min']}.";
                break;
            case 'fecha':
                $f = DateTime::createFromFormat('Y-m-d', $valor);
                if (!$f || $f->format('Y-m-d') !== $valor) $errores[] = 'La fecha no es valida (AAAA-MM-DD).';
                break;
        }
        $datos[$nombre] = $valor;
    }
    return [$datos, $errores];
}

function registrar(mysqli $db, string $tabla, array $def, array $datos): void
{
    $cols = array_keys($def['campos']);
    $sql = "INSERT INTO $tabla (" . implode(', ', $cols) . ') VALUES (' . implode(', ', array_fill(0, count($cols), '?')) . ')';
    $tipos = '';
    $vals = [];
    foreach ($cols as $c) { $tipos .= tipoBind($def['campos'][$c]['tipo']); $vals[] = $datos[$c]; }
    $st = $db->prepare($sql);
    $st->bind_param($tipos, ...$vals);
    $st->execute();
}

function modificar(mysqli $db, string $tabla, array $def, int $id, array $datos): void
{
    $cols = array_keys($def['campos']);
    $set = implode(', ', array_map(fn($c) => "$c = ?", $cols));
    $sql = "UPDATE $tabla SET $set WHERE {$def['pk']} = ?";
    $tipos = '';
    $vals = [];
    foreach ($cols as $c) { $tipos .= tipoBind($def['campos'][$c]['tipo']); $vals[] = $datos[$c]; }
    $tipos .= 'i';
    $vals[] = $id;
    $st = $db->prepare($sql);
    $st->bind_param($tipos, ...$vals);
    $st->execute();
}

function eliminar(mysqli $db, string $tabla, array $def, int $id): void
{
    $st = $db->prepare("DELETE FROM $tabla WHERE {$def['pk']} = ?");
    $st->bind_param('i', $id);
    $st->execute();
}

function obtenerPorId(mysqli $db, string $tabla, array $def, int $id): ?array
{
    $st = $db->prepare("SELECT * FROM $tabla WHERE {$def['pk']} = ?");
    $st->bind_param('i', $id);
    $st->execute();
    return $st->get_result()->fetch_assoc() ?: null;
}
function mensajeError(mysqli_sql_exception $e): string
{
    return match ($e->getCode()) {
        1451 => 'No se puede eliminar: el registro esta relacionado con otros datos (por ejemplo, un cliente con pedidos).',
        1452 => 'El cliente seleccionado no existe.',
        1062 => 'Ya existe un registro con ese valor unico (por ejemplo, email repetido).',
        default => 'Error de base de datos: ' . $e->getMessage(),
    };
}

function h($valor): string
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}
