<?php

namespace Core;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JWTManager
{
    private static string $secretKey;
    private static string $algorithm = 'HS256';

    public static function init(): void
    {
        // Usa a chave do .env ou a chave específica como fallback
        self::$secretKey = Env::get('JWT_SECRET', 'cesla_teste_001');
    }

    public static function encode(array $payload): string
    {
        self::init();
        
        $issuedAt = time();
        $expire = $issuedAt + (60 * 60); // 1 hora

        $payload = array_merge([
            'iat' => $issuedAt,
            'exp' => $expire,
            'iss' => Env::get('APP_URL', 'http://localhost:8000')
        ], $payload);

        return JWT::encode($payload, self::$secretKey, self::$algorithm);
    }

    public static function decode(string $token): object
    {
        self::init();
        
        try {
            return JWT::decode($token, new Key(self::$secretKey, self::$algorithm));
        } catch (Exception $e) {
            throw new Exception('Token inválido: ' . $e->getMessage());
        }
    }

    public static function validate(string $token): bool
    {
        try {
            self::decode($token);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function getPayload(string $token): array
    {
        $decoded = self::decode($token);
        return (array) $decoded;
    }
}