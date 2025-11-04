<?php

namespace Middleware;

use Core\JWTManager;
use Enum\JsonResponse;

class AuthMiddleware
{
    private $jwtManager;

    public function __construct()
    {
        $this->jwtManager = new JWTManager();
    }

    public function handle()
    {
        $token = $this->jwtManager->getTokenFromHeader();

        if (!$token) {
            JsonResponse::unauthorized('Token de autenticação não fornecido');
            exit;
        }

        $userData = $this->jwtManager->validateToken($token);

        if (!$userData) {
            JsonResponse::unauthorized('Token inválido ou expirado');
            exit;
        }

        if (!defined('CURRENT_USER')) {
            define('CURRENT_USER', $userData);
        }

        $GLOBALS['current_user'] = $userData;

        return $userData;
    }
}