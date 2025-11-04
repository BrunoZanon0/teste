<?php

namespace Migrations;

use Migrate\Migration;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        $this->execute("
            CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
    }

    public function down(): void
    {
        $this->execute("DROP TABLE IF EXISTS users");
    }
}