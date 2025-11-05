# Atualize o autoload
composer dump-autoload

# Teste todos
composer test

# Teste unitários
composer test-unit

# Teste específico
./vendor/bin/phpunit tests/Unit/Controllers/PedidoControllerTest.php