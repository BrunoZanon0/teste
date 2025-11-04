<?php

namespace Middleware;

class CorsMiddleware
{
    public function handle()
    {
        // Permitir de qualquer origem
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400'); // 1 dia de cache
        } else {
            header("Access-Control-Allow-Origin: *");
        }

        // Sempre incluir os headers padrão
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Access-Control-Allow-Headers');
        header('Content-Type: application/json; charset=UTF-8');

        // Responder requisições OPTIONS (preflight)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            echo json_encode(['status' => 'ok']);
            exit();
        }
    }
}
