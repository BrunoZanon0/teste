<?php

namespace Migrate;

use PDO;
use Core\Database;

class MigrationManager
{
    private PDO $pdo;
    private string $migrationsPath;

    public function __construct()
    {
        $database = new Database();
        $this->pdo = $database->getConnection();
        $this->migrationsPath = __DIR__ . '/migrations/';
        $this->createMigrationsTable();
    }

    private function createMigrationsTable(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INT NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    public function runMigrations(): void
    {
        require_once __DIR__ . '/Migration.php';
        $executedMigrations = $this->getExecutedMigrations();
        $migrationFiles = $this->getMigrationFiles();

        $pendingMigrations = array_diff($migrationFiles, $executedMigrations);
        
        if (empty($pendingMigrations)) {
            echo "No migrations to run.\n";
            return;
        }

        $batch = $this->getNextBatchNumber();

        foreach ($pendingMigrations as $migration) {
            $this->runMigration($migration, $batch);
        }

        echo "Ran " . count($pendingMigrations) . " migration(s)\n";
    }


    private function runMigration(string $migrationName, int $batch): void
    {
        require_once $this->migrationsPath . $migrationName;

        $className = $this->getClassNameFromFileName($migrationName);
        $migration = new $className();

        echo "Running migration: $migrationName\n";
        $migration->up();

        $stmt = $this->pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
        $stmt->execute([$migrationName, $batch]);

        echo "✓ Completed: $migrationName\n";
    }


    private function getExecutedMigrations(): array
    {
        $stmt = $this->pdo->query("SELECT migration FROM migrations ORDER BY migration");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function getMigrationFiles(): array
    {
        $files = scandir($this->migrationsPath);
        return array_filter($files, function($file) {
            return pathinfo($file, PATHINFO_EXTENSION) === 'php';
        });
    }

    private function getClassNameFromFileName(string $fileName): string
    {
        $filePath = $this->migrationsPath . $fileName;
        $content = file_get_contents($filePath);
        
        if (preg_match('/class\s+([a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)/', $content, $matches)) {
            return 'Migrate\\Migrations\\' . $matches[1];
        }
        
        throw new \Exception("Não foi possível encontrar o nome da classe no arquivo: $fileName");
    }

    private function getNextBatchNumber(): int
    {
        $stmt = $this->pdo->query("SELECT MAX(batch) FROM migrations");
        return (int)$stmt->fetchColumn() + 1;
    }

}