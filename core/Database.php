<?php

namespace Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            self::$connection = self::createConnection();
        }

        return self::$connection;
    }

    private static function createConnection(): PDO
    {
        // No Docker, sempre use as configurações do Docker
        $host = Env::get('DB_HOST', 'mysql_db');
        $port = Env::get('DB_PORT', '3306');
        $database = Env::get('DB_DATABASE', 'api_database');
        $username = Env::get('DB_USERNAME', 'api_user');
        $password = Env::get('DB_PASSWORD', 'api_password');

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

        try {
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            return $pdo;
        } catch (PDOException $e) {
            throw new PDOException("Connection failed: " . $e->getMessage());
        }
    }

    public static function disconnect(): void
    {
        self::$connection = null;
    }
}