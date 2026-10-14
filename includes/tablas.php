<?php
return [
    'clientes' => [
        'titulo' => 'Clientes',
        'pk' => 'id_cliente',
        'consulta' => 'SELECT * FROM clientes ORDER BY id_cliente',
        'orden' => ['campo' => 'nombre', 'numerico' => false, 'texto' => 'nombre (A-Z)'],
        'campos' => [
            'nombre' => ['label' => 'Nombre', 'tipo' => 'texto', 'max' => 100],
            'email'  => ['label' => 'Email', 'tipo' => 'email', 'max' => 100],
            'edad'   => ['label' => 'Edad', 'tipo' => 'entero', 'min' => 0, 'max' => 120],
        ],
    ],
    'productos' => [
        'titulo' => 'Productos',
        'pk' => 'id_producto',
        'consulta' => 'SELECT * FROM productos ORDER BY id_producto',
        'orden' => ['campo' => 'precio', 'numerico' => true, 'texto' => 'precio (menor a mayor)'],
        'campos' => [
            'nombre'    => ['label' => 'Nombre', 'tipo' => 'texto', 'max' => 100],
            'categoria' => ['label' => 'Categoria', 'tipo' => 'texto', 'max' => 50],
            'precio'    => ['label' => 'Precio', 'tipo' => 'decimal', 'min' => 0],
            'stock'     => ['label' => 'Stock', 'tipo' => 'entero', 'min' => 0],
        ],
    ],
    'pedidos' => [
        'titulo' => 'Pedidos',
        'pk' => 'id_pedido',
        'consulta' => 'SELECT p.id_pedido, p.id_cliente, c.nombre AS cliente, p.fecha, p.total
                       FROM pedidos p JOIN clientes c ON c.id_cliente = p.id_cliente ORDER BY p.id_pedido',
        'orden' => ['campo' => 'total', 'numerico' => true, 'texto' => 'total (menor a mayor)'],
        'campos' => [
            'id_cliente' => ['label' => 'Cliente', 'tipo' => 'cliente'],
            'fecha'      => ['label' => 'Fecha', 'tipo' => 'fecha'],
            'total'      => ['label' => 'Total', 'tipo' => 'decimal', 'min' => 0],
        ],
        // Columnas que se muestran en las tablas (incluye el nombre del cliente)
        'columnas' => ['id_pedido' => 'ID', 'cliente' => 'Cliente', 'fecha' => 'Fecha', 'total' => 'Total'],
    ],
];
