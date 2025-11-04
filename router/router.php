<?php

use Bramus\Router\Router;
use Controllers\PedidoController;
use Controllers\AuthController;
use Enum\JsonResponse;
use Middleware\AuthMiddleware;
use Middleware\CorsMiddleware;

$router = new Router();

$router->before('GET|POST|PUT|DELETE', '/.*', function() {
    (new CorsMiddleware())->handle();
});

// Teste se está chegando direitinho aqui oh :D

$router->get('/', function() {
    JsonResponse::success([
        'message' => 'API funcionando',
        'version' => '1.0',
    ]);
});

$router->post('/auth/register', [new AuthController(), 'register']);
$router->post('/auth/login', [new AuthController(), 'login']);

$router->before('GET|POST|PUT|DELETE', '/pedidos.*', function() {
    (new AuthMiddleware())->handle();
});

$router->get('/pedidos', [new PedidoController(), 'index']);
$router->get('/pedidos/(\d+)', [new PedidoController(), 'show']);
$router->post('/pedidos', [new PedidoController(), 'store']);
$router->put('/pedidos/(\d+)', [new PedidoController(), 'update']);

$router->set404(function() {
    JsonResponse::notFound();
});

$router->run();

return $router;