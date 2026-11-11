<?php
// Módulo ventas: utiliza el CRUD compartido y procedimientos almacenados.
/** CRUD de ventas; las operaciones se ejecutan con SP. */
require_once __DIR__.'/../includes/crud.php';
moduloCrud('ventas','Ventas');
