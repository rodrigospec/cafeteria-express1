<?php
// Módulo inventario: utiliza el CRUD compartido y procedimientos almacenados.

/** CRUD de movimientos_inventario; las operaciones se ejecutan con SP. */
require_once __DIR__ . '/../includes/crud.php';
moduloCrud('movimientos_inventario', 'Inventario');
