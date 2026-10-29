<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/** Kết nối PDO dùng chung (Singleton) */
final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo === null) {
            $c = require BASE_PATH . '/config/database.php';
            $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['name']};charset={$c['charset']}";
            self::$pdo = new PDO($dsn, $c['user'], $c['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                // Đồng bộ múi giờ MySQL với PHP để created_at / paid_at khớp nhau
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '" . date('P') . "'",
            ]);
        }
        return self::$pdo;
    }
}
