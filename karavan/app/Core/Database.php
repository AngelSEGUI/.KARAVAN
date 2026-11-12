<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/** Connexion PDO unique (singleton) partagée par tous les modèles. */
final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $cfg = Config::get('db');

        if ($cfg['driver'] === 'sqlite') {
            $dir = dirname($cfg['path']);
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $pdo = new PDO('sqlite:' . $cfg['path']);
            $pdo->exec('PRAGMA foreign_keys = ON');
        } else {
            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $cfg['host'], $cfg['name']);
            $pdo = new PDO($dsn, $cfg['user'], $cfg['pass']);
        }

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return self::$pdo = $pdo;
    }
}
