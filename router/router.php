<?php

use Bramus\Router\Router;
use Controllers\PedidoController;
use Controllers\AuthController;
use Enum\JsonResponse;
use Middleware\AuthMiddleware;
use Middleware\CorsMiddleware;

$router = new Router();

$router->setNamespace('/api');

$router->before('GET|POST|PUT|DELETE', '/.*', function() {
    (new CorsMiddleware())->handle();
});

$router->mount('/api', function() use ($router) {

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

    $router->get('/pedidos', [new PedidoController(), 'getAll']);
    $router->get('/pedidos/(\d+)', [new PedidoController(), 'getOneOrder']);
    $router->post('/pedidos', [new PedidoController(), 'createNewOrder']);
    $router->put('/pedidos/(\d+)', [new PedidoController(), 'updateOrder']);
    // $router->delete('/pedidos/(\d+)', [new PedidoController(), 'delete']); // Desabilitei pois pedia no pdf teste

});
$router->set404(function() {
    JsonResponse::notFound();
});

$router->run();

return $router;