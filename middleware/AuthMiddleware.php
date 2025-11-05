<?php

namespace Middleware;

use Enum\JsonResponse;
use Enum\HttpStatusCode;
use Core\Logger;

class AuthMiddleware
{
    private Logger $logger;

    public function __construct()
    {
        $this->logger = new Logger();
    }

    public function handle(): void
    {
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? null;
        $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
        $route = $_SERVER['REQUEST_URI'] ?? '/';
        
        if (!$token) {
            $this->logger->logAuthError($method, $route, 'AUTH_MIDDLEWARE', 'Token não fornecido');
            JsonResponse::error('Token de autenticação necessário', HttpStatusCode::UNAUTHORIZED);
            exit;
        }
        
        try {
            $decoded = $this->validateJWT($token);
            $GLOBALS['current_user'] = $decoded;
            
            $this->logger->logAuth($method, $route, 'AUTH_SUCCESS', $decoded['user_id'] ?? null);
            
        } catch (\Exception $e) {
            $this->logger->logAuthError($method, $route, 'AUTH_MIDDLEWARE', $e->getMessage());
            JsonResponse::error('Token inválido', HttpStatusCode::UNAUTHORIZED);
            exit;
        }
    }
    
    private function validateJWT(string $token): array
    {
        $token = str_replace('Bearer ', '', $token);
        
        return ['user_id' => 1, 'email' => 'usuario@exemplo.com'];
    }
}