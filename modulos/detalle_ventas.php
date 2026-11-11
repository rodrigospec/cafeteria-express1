<?php
// Módulo detalle_ventas: utiliza el CRUD compartido y procedimientos almacenados.

/** CRUD de detalle_ventas; las operaciones se ejecutan con SP. */
require_once __DIR__ . '/../includes/crud.php';
moduloCrud('detalle_ventas', 'Detalle de ventas');
