<?php

namespace Core;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JWTManager
{
    private string $secretKey;
    private string $algorithm;
    private int $expirationTime;

    public function __construct()
    {
        $this->secretKey = $_ENV['JWT_SECRET'] ?? 'sua_chave_secreta_muito_segura_aqui';
        $this->algorithm = 'HS256';
        $this->expirationTime = 3600; // 1 hora em segundos
    }

    public function generateToken(array $payload): string
    {
        $issuedAt = time();
        $expire = $issuedAt + $this->expirationTime;

        $tokenPayload = [
            'iss' => $_ENV['APP_URL'] ?? 'http://localhost', // Issuer
            'aud' => $_ENV['APP_URL'] ?? 'http://localhost', // Audience
            'iat' => $issuedAt, // Issued at
            'exp' => $expire, // Expire time
            'data' => $payload // User data
        ];

        return JWT::encode($tokenPayload, $this->secretKey, $this->algorithm);
    }

    public function validateToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            return (array) $decoded->data;
        } catch (Exception $e) {
            error_log('JWT Validation Error: ' . $e->getMessage());
            return null;
        }
    }

    public function getTokenFromHeader(): ?string
    {
        $headers = getallheaders();
        
        // Verifica no header Authorization
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                return $matches[1];
            }
        }
        
        // Verifica se veio por query string (fallback)
        if (isset($_GET['token'])) {
            return $_GET['token'];
        }

        return null;
    }

    public function getExpirationTime(): int
    {
        return $this->expirationTime;
    }
}