<?php

namespace Tests;

class TestHelper
{
    public static function disableDatabaseConnections(): void
    {
        // Mock do PDO para evitar conexões reais
        if (!class_exists('Tests\PDOMock', false)) {
            self::createPDOMock();
        }

        // Mock da classe Database se existir
        if (class_exists('Core\Database')) {
            self::createDatabaseMock();
        }
    }

    private static function createPDOMock(): void
    {
        eval('
            namespace Tests;
            class PDOMock extends \PDO
            {
                public function __construct()
                {
                    // Não faz nada - evita conexão real
                }
            }
        ');
    }

    private static function createDatabaseMock(): void
    {
        $mock = new class {
            public function getConnection() {
                return new \PDO('sqlite::memory:');
            }
        };
        
        // Substitui a instância global se necessário
        $GLOBALS['DATABASE_MOCK'] = $mock;
    }
}