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
        $this->pdo = Database::getConnection();
        $this->migrationsPath = __DIR__ . '/../migrate/migrations/';
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

    public function rollback(): void
    {
        $batch = $this->getLastBatchNumber();
        $migrations = $this->getMigrationsByBatch($batch);

        foreach ($migrations as $migration) {
            $this->rollbackMigration($migration);
        }

        echo "Rolled back " . count($migrations) . " migration(s)\n";
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

    private function rollbackMigration(string $migrationName): void
    {
        require_once $this->migrationsPath . $migrationName;

        $className = $this->getClassNameFromFileName($migrationName);
        $migration = new $className();

        echo "Rolling back: $migrationName\n";
        $migration->down();

        $stmt = $this->pdo->prepare("DELETE FROM migrations WHERE migration = ?");
        $stmt->execute([$migrationName]);

        echo "✓ Rolled back: $migrationName\n";
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
        return 'Migrations\\' . pathinfo($fileName, PATHINFO_FILENAME);
    }

    private function getNextBatchNumber(): int
    {
        $stmt = $this->pdo->query("SELECT MAX(batch) FROM migrations");
        return (int)$stmt->fetchColumn() + 1;
    }

    private function getLastBatchNumber(): int
    {
        $stmt = $this->pdo->query("SELECT MAX(batch) FROM migrations");
        return (int)$stmt->fetchColumn();
    }

    private function getMigrationsByBatch(int $batch): array
    {
        $stmt = $this->pdo->prepare("SELECT migration FROM migrations WHERE batch = ? ORDER BY id DESC");
        $stmt->execute([$batch]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}