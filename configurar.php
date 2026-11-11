<?php

/** Instalación local: se desactiva tras crear la contraseña. Nunca permite instalación remota. */
require_once __DIR__ . '/includes/comun.php';
require_once __DIR__ . '/includes/autenticacion.php';
iniciar();
if (!puedeConfigurarLocal()) {
    http_response_code(403);
    exit('Configuración disponible únicamente desde localhost. En Render usa APP_ADMIN_PASSWORD.');
}
if (hashAdministrador() !== '') {
    header('Location: login.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verificarCsrf();
        $pass = (string)($_POST['password'] ?? '');
        $confirm = (string)($_POST['confirmar'] ?? '');
        if (strlen($pass) < 12 || strlen($pass) > 128) throw new RuntimeException('La contraseña debe tener entre 12 y 128 caracteres.');
        if (!hash_equals($pass, $confirm)) throw new RuntimeException('Las contraseñas no coinciden.');
        $path = __DIR__ . '/config/admin-auth.php';
        if (file_exists($path)) throw new RuntimeException('La contraseña ya fue configurada.');
        $content = '<?php return ' . var_export(password_hash($pass, PASSWORD_DEFAULT), true) . ';' . "\n";
        if (file_put_contents($path, $content, LOCK_EX) === false) throw new RuntimeException('No se pudo guardar. Verifica permisos de la carpeta config.');
        @chmod($path, 0600);
        header('Location: login.php');
        exit;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Configurar | Cafetería Express</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>

<body>
    <main style="max-width:460px;margin:10vh auto;padding:22px">
        <section class="panel">
            <h1>☕ Cafetería Express</h1>
            <h2>Crear contraseña de administrador</h2>
            <p>Configuración inicial de XAMPP. Solo se realiza una vez.</p><?php if ($error): ?><p role="alert"><?= e($error) ?></p><?php endif; ?><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf()) ?>"><label class="field">Nueva contraseña<input type="password" name="password" minlength="12" maxlength="128" required autocomplete="new-password"></label><label class="field">Confirmar contraseña<input type="password" name="confirmar" minlength="12" maxlength="128" required autocomplete="new-password"></label><button class="button" type="submit">Guardar y continuar</button></form>
        </section>
    </main>
</body>

</html>