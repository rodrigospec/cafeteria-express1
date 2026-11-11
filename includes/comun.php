<?php

/** Componentes visuales compartidos y seguridad de formularios. */
function e(mixed $x): string
{
  return htmlspecialchars((string)$x, ENT_QUOTES, 'UTF-8');
}
function raiz(): string
{
  return str_contains(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/modulos/') ? '../' : '';
}
function iniciar(): void
{
  if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ini_set('session.cookie_secure', '1');
    session_start();
  }
}
function csrf(): string
{
  iniciar();
  if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
  return $_SESSION['csrf'];
}
function exigirAcceso(): void
{
  iniciar();
  if (empty($_SESSION['autorizado'])) {
    header('Location: ' . raiz() . 'login.php');
    exit;
  }
}
function verificarCsrf(): void
{
  iniciar();
  if (!hash_equals(csrf(), (string)($_POST['csrf'] ?? ''))) {
    http_response_code(403);
    throw new RuntimeException('Solicitud inválida: token CSRF.');
  }
}
function abrirPagina(string $titulo, string $activo): void
{
  $base = raiz();
  $links = ['inicio' => ['Inicio', 'index.php'], 'categorias' => ['Categorías', 'modulos/categorias.php'], 'productos' => ['Productos', 'modulos/productos.php'], 'inventario' => ['Inventario', 'modulos/inventario.php'], 'ventas' => ['Ventas', 'modulos/ventas.php'], 'detalle_ventas' => ['Detalle de ventas', 'modulos/detalle_ventas.php']];
?>
  <!doctype html>
  <html lang="es">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($titulo) ?> | Cafetería Express</title>
    <link rel="stylesheet" href="<?= e($base) ?>assets/css/estilos.css">
  </head>

  <body>
    <div class="layout">
      <aside class="sidebar" id="menuPrincipal"><a class="brand" href="<?= e($base) ?>index.php"><span class="brand-icon">☕</span><strong>Cafetería Express</strong><small>Administración</small></a>
        <nav aria-label="Navegación principal"><?php foreach ($links as $key => $item): ?><a class="nav-link <?= $key === $activo ? 'active' : '' ?>" href="<?= e($base . $item[1]) ?>"><?= e($item[0]) ?></a><?php endforeach; ?></nav>
        <p class="sidebar-note">Proyecto académico · PHP / MySQL</p>
      </aside>
      <div class="content">
        <header class="topbar"><button type="button" class="menu-button" id="menuButton" aria-expanded="false" aria-controls="menuPrincipal">☰ Menú</button><span>☕ Cafetería Express</span><span class="status">Sistema de gestión · <a href="<?= e($base) ?>salir.php">Cerrar sesión</a></span></header>
        <main class="main">
          <div class="page-head">
            <div>
              <p class="eyebrow">ADMINISTRACIÓN</p>
              <h1><?= e($titulo) ?></h1>
            </div>
          </div>
        <?php }
      function cerrarPagina(): void
      {
        $base = raiz(); ?>
        </main>
        <footer>© <?= date('Y') ?> Cafetería Express · Datos reales desde MySQL</footer>
      </div>
    </div>
    <script src="<?= e($base) ?>assets/js/app.js"></script>
  </body>

  </html>
<?php }
      function avisoError(string $msg): void
      {
        echo '<div class="notice error" role="alert">' . e($msg) . '</div>';
      }
      function avisoOk(string $msg): void
      {
        echo '<div class="notice" role="status">' . e($msg) . '</div>';
      }
