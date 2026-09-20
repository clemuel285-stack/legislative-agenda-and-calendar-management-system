<?php
declare(strict_types=1);

require_once __DIR__ . '/app.php';

if (!defined('DB_HOST')) define('DB_HOST', 'mariadb-rcbdyxxo.internal');
if (!defined('DB_PORT')) define('DB_PORT', '3306');
if (!defined('DB_NAME')) define('DB_NAME', 'hf_db_rcbdyxxo');
if (!defined('DB_USER')) define('DB_USER', 'hf_rltqcviass');
if (!defined('DB_PASS')) define('DB_PASS', 'L1gr3vPUUKYvsk9tty2u4M6993psMHsK');

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        DB_HOST,
        DB_PORT,
        DB_NAME
    );

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $exception) {
        error_log('[LACMS Database] ' . $exception->getMessage());
        http_response_code(500);
        exit('Unable to connect to the shared legislative database. Check XAMPP MySQL and config/database.php.');
    }

    return $pdo;
}
