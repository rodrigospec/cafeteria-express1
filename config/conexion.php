<?php

/**
 * Conexion PDO para XAMPP, Render y Aiven MySQL.
 *
 * Compatible con PHP 8.2.
 * No guardar contrasenas en GitHub.
 */

function conectarBD(): PDO
{
    // 1. Configuracion privada de XAMPP
    $local = 'C:/xampp/private/aiven-config.php';

    $privado = is_readable($local)
        ? require $local
        : [];

    // 2. Variables de entorno
    $host = getenv('DB_HOST') ?: 'localhost';
    $port = (int) (getenv('DB_PORT') ?: 3306);
    $db = getenv('DB_NAME') ?: 'cafeteria_express';
    $user = getenv('DB_USER') ?: 'root';

    $pass = getenv('DB_PASSWORD');

    if ($pass === false) {
        $pass = (string) ($privado['password'] ?? '');
    }

    // 3. Compatibilidad con Aiven en XAMPP
    if ($privado && !getenv('DB_HOST')) {
        $host = 'mysql-28c0e89c-cafeteria-express.b.aivencloud.com';
        $port = 11562;
        $user = 'avnadmin';
    }

    // 4. Certificado CA
    $ca = getenv('DB_SSL_CA')
        ?: (string) ($privado['certificado'] ?? '');

    $opts = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    // 5. Validacion SSL
    if ($ca !== '') {

        if (!is_readable($ca)) {

            error_log(
                'Diagnostico CA: ' .
                'existe=' . (file_exists($ca) ? 'SI' : 'NO') .
                '; archivo=' . (is_file($ca) ? 'SI' : 'NO') .
                '; legible=' . (is_readable($ca) ? 'SI' : 'NO')
            );

            throw new RuntimeException(
                'No se encuentra el certificado CA configurado.'
            );
        }

        $opts[PDO::MYSQL_ATTR_SSL_CA] = $ca;
        $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;

    } elseif (getenv('APP_ENV') === 'production') {

        throw new RuntimeException(
            'Configura DB_SSL_CA para la conexion cifrada de produccion.'
        );
    }

    // 6. Conexion MySQL mediante PDO
    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

    return new PDO(
        $dsn,
        $user,
        $pass,
        $opts
    );
}
