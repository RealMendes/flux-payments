<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use App\Application\Settings\SettingsInterface;
use PDO;
use PDOException;

class DatabaseConnection
{
    private static ?PDO $connection = null;

    public static function getConnection(SettingsInterface $settings): PDO
    {
        if (self::$connection === null) {
            $dbSettings = $settings->get('database');

            $host = $dbSettings['host'];
            $dbname = $dbSettings['name'];
            $username = $dbSettings['user'];
            $password = $dbSettings['password'];
            $port = $dbSettings['port'];

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                self::$connection = new PDO($dsn, $username, $password, $options);
            } catch (PDOException $e) {
                throw new PDOException('Database connection failed: ' . $e->getMessage());
            }
        }

        return self::$connection;
    }
}
