<?php
// Página principal: muestra únicamente las cinco tablas que exige la evaluación.
require_once __DIR__.'/includes/comun.php';
exigirAcceso();
abrirPagina('Inicio','inicio');
$modulos = [
 'categorias'=>'Categorías',
 'productos'=>'Productos',
 'inventario'=>'Movimientos de inventario',
 'ventas'=>'Ventas',
 'detalle_ventas'=>'Detalle de ventas'
];
?>
<section class="panel"><h2>Bienvenido</h2><p>Selecciona una sección para registrar, consultar, editar o eliminar datos.</p></section>
<section class="cards">
<?php foreach ($modulos as $archivo=>$titulo): ?>
<div class="metric"><h3><?=e($titulo)?></h3><a class="button secondary" href="modulos/<?=e($archivo)?>.php">Abrir</a></div>
<?php endforeach; ?>
</section>
<?php cerrarPagina();
