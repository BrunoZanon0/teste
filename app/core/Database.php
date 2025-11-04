<?php

namespace Core;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $connection;

    public function __construct()
    {
        try {
            // Use os nomes corretos das variáveis do seu .env
            $host = $_ENV['DB_HOST'] ?? 'mysql_db';
            $dbname = $_ENV['DB_DATABASE'] ?? 'api_database';
            $username = $_ENV['DB_USERNAME'] ?? 'api_user';
            $password = $_ENV['DB_PASSWORD'] ?? 'api_password';
            $port = $_ENV['DB_PORT'] ?? '3306';

            $this->connection = new PDO(
                "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

        } catch (PDOException $e) {
            throw new PDOException("Connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public static function getConnectionStatic()
    {
        return self::getInstance()->getConnection();
    }
}