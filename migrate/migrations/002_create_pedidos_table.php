<?php

namespace Migrate\Migrations;

use Migrate\Migration;

class CreatePedidosTable extends Migration
{
    public function up(): void
    {
        $this->execute("
            CREATE TABLE pedidos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                description TEXT NOT NULL,
                status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
                total DECIMAL(10,2) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )
        ");
    }

    public function down(): void
    {
        $this->execute("DROP TABLE IF EXISTS pedidos");
    }
}