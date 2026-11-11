<?php
// Módulo categorias: utiliza el CRUD compartido y procedimientos almacenados.

/** CRUD de categorias; las operaciones se ejecutan con SP. */
require_once __DIR__ . '/../includes/crud.php';
moduloCrud('categorias', 'Categorias');
