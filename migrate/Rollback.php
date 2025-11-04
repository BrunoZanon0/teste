<?php

require_once __DIR__ . '/vendor/autoload.php';

use Core\Env;
use Migrate\MigrationManager;

try {
    Env::load();
    $manager = new MigrationManager();
    $manager->rollback();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}