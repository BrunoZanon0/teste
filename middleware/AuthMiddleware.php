<?php

namespace Middleware;

use Core\JWTManager;
use Enum\JsonResponse;
use Enum\HttpStatusCode;
use Exception;

class AuthMiddleware
{
    public function handle(): void
    {
        $token = $this->getBearerToken();
        
        if (!$token) {
            JsonResponse::error(
                'Token de autenticação não fornecido',
                HttpStatusCode::UNAUTHORIZED
            );
        }

        try {
            $payload = JWTManager::getPayload($token);
            
            $GLOBALS['user'] = $payload;
            
        } catch (Exception $e) {
            JsonResponse::error(
                'Token inválido ou expirado: ' . $e->getMessage(),
                HttpStatusCode::UNAUTHORIZED
            );
        }
    }

    private function getBearerToken(): ?string
    {
        $headers = $this->getAllHeaders();
        
        if (isset($headers['Authorization'])) {
            if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }

        if (isset($_GET['token'])) {
            return $_GET['token'];
        }

        return null;
    }

    private function getAllHeaders(): array
    {
        if (!function_exists('getallheaders')) {
            $headers = [];
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
            return $headers;
        }
        
        return getallheaders();
    }
}