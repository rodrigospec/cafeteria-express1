<?php
// Módulo productos: utiliza el CRUD compartido y procedimientos almacenados.

/** CRUD de productos; las operaciones se ejecutan con SP. */
require_once __DIR__ . '/../includes/crud.php';
moduloCrud('productos', 'Productos');
