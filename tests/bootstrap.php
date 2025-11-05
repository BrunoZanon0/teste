<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Desabilita QUALQUER conexão com banco de dados
require_once __DIR__ . '/TestHelper.php';
Tests\TestHelper::disableDatabaseConnections();

// Configurações para testes
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';

// Define constantes necessárias para os testes
if (!defined('CURRENT_USER')) {
    define('CURRENT_USER', ['user_id' => 1, 'email' => 'test@example.com']);
}

// Configura o timezone
date_default_timezone_set('America/Sao_Paulo');

// Mock das variáveis de ambiente
putenv("DB_HOST=127.0.0.1");
putenv("DB_DATABASE=test_memory");
putenv("DB_USERNAME=test");
putenv("DB_PASSWORD=test");

// Previne qualquer tentativa de conexão real
ini_set('display_errors', '0');
error_reporting(0);