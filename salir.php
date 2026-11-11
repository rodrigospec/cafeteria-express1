<?php

/** Cierra la sesión de administración. */
require_once __DIR__ . '/includes/comun.php';
iniciar();
$_SESSION = [];
session_regenerate_id(true);
session_destroy();
header('Location: login.php');
exit;
