<?php

namespace Controllers;

use Core\Database;
use Core\JWTManager;
use Enum\JsonResponse;
use Enum\HttpStatusCode;
use PDO;

class AuthController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function register(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['name']) || !isset($data['email']) || !isset($data['password'])) {
            JsonResponse::error(
                'Nome, email e senha são obrigatórios',
                HttpStatusCode::BAD_REQUEST
            );
        }

        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        
        if ($stmt->fetch()) {
            JsonResponse::error(
                'Usuário já cadastrado',
                HttpStatusCode::CONFLICT
            );
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO users (name, email, password) 
            VALUES (?, ?, ?)
        ");

        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt->execute([
            $data['name'],
            $data['email'],
            $passwordHash
        ]);

        $userId = $this->pdo->lastInsertId();

        $token = JWTManager::encode([
            'user_id' => $userId,
            'name' => $data['name'],
            'email' => $data['email']
        ]);

        JsonResponse::success([
            'message' => 'Usuário criado com sucesso',
            'user' => [
                'id' => $userId,
                'name' => $data['name'],
                'email' => $data['email']
            ],
            'token' => $token
        ], HttpStatusCode::CREATED);
    }

    public function login(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['email']) || !isset($data['password'])) {
            JsonResponse::error(
                'Email e senha são obrigatórios',
                HttpStatusCode::BAD_REQUEST
            );
        }

        $stmt = $this->pdo->prepare("
            SELECT id, name, email, password 
            FROM users 
            WHERE email = ?
        ");
        
        $stmt->execute([$data['email']]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($data['password'], $user['password'])) {
            JsonResponse::error(
                'Credenciais inválidas',
                HttpStatusCode::UNAUTHORIZED
            );
        }

        $token = JWTManager::encode([
            'user_id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email']
        ]);

        JsonResponse::success([
            'message' => 'Login realizado com sucesso',
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email']
            ],
            'token' => $token
        ]);
    }

    public function profile(): void
    {
        if (!isset($GLOBALS['user'])) {
            JsonResponse::error(
                'Usuário não autenticado',
                HttpStatusCode::UNAUTHORIZED
            );
        }

        $user = $GLOBALS['user'];

        JsonResponse::success([
            'user' => [
                'id' => $user['user_id'],
                'name' => $user['name'],
                'email' => $user['email']
            ]
        ]);
    }
}