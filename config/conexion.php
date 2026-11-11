
<?php

/**
 * Conexión PDO para XAMPP y Render.
 *
 * Compatible con:
 * - PHP 8.2
 * - MySQL / Aiven
 * - XAMPP local
 * - Render Docker
 *
 * Nunca guardar contraseñas en Git.
 */

function conectarBD(): PDO
{
    // =====================================================
    // 1. CONFIGURACIÓN PRIVADA LOCAL (XAMPP)
    // =====================================================

    $local = 'C:/xampp/private/aiven-config.php';

    $privado = is_readable($local)
        ? require $local
        : [];

    // =====================================================
    // 2. VARIABLES DE ENTORNO
    // =====================================================

    // Las variables DB_* tienen prioridad sobre XAMPP.

    $host = getenv('DB_HOST') ?: 'localhost';

    $port = (int) (getenv('DB_PORT') ?: 3306);

    $db = getenv('DB_NAME') ?: 'cafeteria_express';

    $user = getenv('DB_USER') ?: 'root';

    $pass = getenv('DB_PASSWORD');

    if ($pass === false) {
        $pass = (string) ($privado['password'] ?? '');
    }

    // =====================================================
    // 3. COMPATIBILIDAD CON AIVEN LOCAL
    // =====================================================

    if ($privado && !getenv('DB_HOST')) {

        $host = 'mysql-28c0e89c-cafeteria-express.b.aivencloud.com';

        $port = 11562;

        $user = 'avnadmin';
    }

    // =====================================================
    // 4. CONFIGURACIÓN DEL CERTIFICADO SSL
    // =====================================================

    $ca = getenv('DB_SSL_CA')
        ?: (string) ($privado['certificado'] ?? '');

    $opts = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    // =====================================================
    // 5. VALIDACIÓN Y DIAGNÓSTICO DEL CERTIFICADO
    // =====================================================

    if ($ca !== '') {

        if (!is_readable($ca)) {

            // Diagnóstico temporal para Render.
            // No muestra contraseñas ni contenido del CA.

            error_log(
                'Diagnostico CA: ' .
                'ruta=' . $ca .
                '; existe=' . (file_exists($ca) ? 'SI' : 'NO') .
                '; archivo=' . (is_file($ca) ? 'SI' : 'NO') .
                '; legible=' . (is_readable($ca) ? 'SI' : 'NO')
            );

            throw new RuntimeException(
                'No se encuentra el certificado CA configurado.'
            );
        }

        // Configuración SSL para Aiven MySQL.

        $opts[PDO::MYSQL_ATTR_SSL_CA] = $ca;

        $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;

    } elseif (getenv('APP_ENV') === 'production') {

        // En producción es obligatorio configurar el CA.

        throw new RuntimeException(
            'Configura DB_SSL_CA para la conexión cifrada de producción.'
        );
    }

    // =====================================================
    // 6. CONEXIÓN PDO A MYSQL
    // =====================================================

    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

    return new PDO(
        $dsn,
        $user,
        $pass,
        $opts
    );
}
