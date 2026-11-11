<?php
// Obtiene el hash de la contraseña sin guardar contraseñas en el código.
function hashAdministrador(): string
{
    $hash = getenv('APP_ADMIN_PASSWORD_HASH');
    if (is_string($hash) && $hash !== '') return $hash;
    // En Render se puede configurar una contraseña simple como variable privada.
    $password = getenv('APP_ADMIN_PASSWORD');
    if (is_string($password) && $password !== '') return password_hash($password, PASSWORD_DEFAULT);
    // En XAMPP el asistente configurar.php crea este archivo local protegido.
    $archivo = __DIR__ . '/../config/admin-auth.php';
    if (is_file($archivo)) {
        $guardado = require $archivo;
        return is_string($guardado) ? $guardado : '';
    }
    return '';
}
// La configuración por navegador está permitida únicamente desde la misma computadora.
function puedeConfigurarLocal(): bool
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    return in_array($ip, ['127.0.0.1', '::1'], true) && getenv('APP_ENV') !== 'production';
}
