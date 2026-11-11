<?php

/** Esquema único de formularios. Los nombres de tablas son constantes, nunca vienen del usuario. */
return [
    'categorias' => ['pk' => 'id_categoria', 'fields' => ['nombre' => ['label' => 'Nombre', 'type' => 'texto'], 'descripcion' => ['label' => 'Descripcion', 'type' => 'texto'], 'activo' => ['label' => 'Activo', 'type' => 'bool']]],
    'productos' => ['pk' => 'id_producto', 'fields' => ['id_categoria' => ['label' => 'Id Categoria', 'type' => 'select', 'source' => 'categorias'], 'nombre' => ['label' => 'Nombre', 'type' => 'texto'], 'descripcion' => ['label' => 'Descripcion', 'type' => 'texto'], 'precio' => ['label' => 'Precio', 'type' => 'decimal'], 'stock_actual' => ['label' => 'Existencias (manual)', 'type' => 'int'], 'stock_minimo' => ['label' => 'Stock Minimo', 'type' => 'int'], 'activo' => ['label' => 'Activo', 'type' => 'bool']]],
    'movimientos_inventario' => ['pk' => 'id_movimiento', 'fields' => ['id_producto' => ['label' => 'Id Producto', 'type' => 'select', 'source' => 'productos'], 'tipo' => ['label' => 'Tipo (AJUSTE suma)', 'type' => 'select', 'options' => ['ENTRADA', 'SALIDA', 'AJUSTE']], 'cantidad' => ['label' => 'Cantidad', 'type' => 'int'], 'motivo' => ['label' => 'Motivo', 'type' => 'texto']]],
    'ventas' => ['pk' => 'id_venta', 'fields' => ['metodo_pago' => ['label' => 'Metodo Pago', 'type' => 'select', 'options' => ['EFECTIVO', 'TARJETA', 'TRANSFERENCIA']], 'estado' => ['label' => 'Estado', 'type' => 'select', 'options' => ['COMPLETADA', 'CANCELADA']]]],
    'detalle_ventas' => ['pk' => 'id_detalle', 'fields' => ['id_venta' => ['label' => 'Id Venta', 'type' => 'select', 'source' => 'ventas'], 'id_producto' => ['label' => 'Id Producto', 'type' => 'select', 'source' => 'productos'], 'cantidad' => ['label' => 'Cantidad', 'type' => 'int'], 'precio_unitario' => ['label' => 'Precio Unitario', 'type' => 'decimal']]],
];
