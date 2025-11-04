<?php

namespace Migrate;

use PDO;
use Core\Database;

abstract class Migration
{
    protected PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    abstract public function up(): void;
    abstract public function down(): void;

    protected function execute(string $sql): void
    {
        try {
            $this->pdo->exec($sql);
        } catch (\PDOException $e) {
            throw new \Exception("Migration failed: " . $e->getMessage());
        }
    }
}