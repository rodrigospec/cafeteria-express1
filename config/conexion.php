<?php

/** Conexión PDO para XAMPP y Render. Nunca guardar contraseñas en Git. */
function conectarBD(): PDO
{
    $local = 'C:/xampp/private/aiven-config.php';
    $privado = is_readable($local) ? require $local : [];
    // Variables DB_* tienen prioridad sobre el archivo privado de XAMPP.
    $host = getenv('DB_HOST') ?: 'localhost';
    $port = (int)(getenv('DB_PORT') ?: 3306);
    $db = getenv('DB_NAME') ?: 'cafeteria_express';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASSWORD');
    if ($pass === false) $pass = (string)($privado['password'] ?? '');
    // Compatible con configuración local previa de Aiven, sin publicar secretos.
    if ($privado && !getenv('DB_HOST')) {
        $host = 'mysql-28c0e89c-cafeteria-express.b.aivencloud.com';
        $port = 11562;
        $user = 'avnadmin';
    }
    $ca = getenv('DB_SSL_CA') ?: (string)($privado['certificado'] ?? '');
    $opts = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];
    if ($ca !== '') {
        if (!is_readable($ca)) throw new RuntimeException('No se encuentra el certificado CA configurado.');
        $opts[PDO::MYSQL_ATTR_SSL_CA] = $ca;
        $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
    } elseif (getenv('APP_ENV') === 'production') {
        throw new RuntimeException('Configura DB_SSL_CA para la conexión cifrada de producción.');
    }
    return new PDO("mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4", $user, $pass, $opts);
}
