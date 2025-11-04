<?php

namespace Controllers;

use Enum\JsonResponse;
use Models\User;
use Core\JWTManager;
use Enum\HttpStatusCode;
use Interfaces\AuthControllerInterface;
use Controllers;
use Exception;

/**
 * AuthController
 * 
 * Responsável por gerenciar autenticação e registro de usuários
 * 
 * @implements AuthControllerInterface
 */

class AuthController extends Controller implements AuthControllerInterface
{
    /**
     * @var User Model de usuário
     */
    private $userModel;

    /**
     * @var JWTManager Gerenciador de tokens JWT
     */
    private $jwtManager;

    /**
     * Construtor
     */
    public function __construct()
    {
        $this->userModel = new User();
        $this->jwtManager = new JWTManager();
    }

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                JsonResponse::error('Método não permitido', HttpStatusCode::METHOD_NOT_ALLOWED);
                return;
            }

            $jsonInput = file_get_contents('php://input');
            $data = json_decode($jsonInput, true);
            
            if (!$data) {
                JsonResponse::error('Dados JSON inválidos ou vazios', HttpStatusCode::BAD_REQUEST);
                return;
            }

            $errors = $this->validateRegisterData($data);
            
            if (!empty($errors)) {
                JsonResponse::validationError($errors);
                return;
            }

            $userId = $this->userModel->create([
                'name' => trim($data['name']),
                'email' => trim($data['email']),
                'password' => password_hash($data['password'], PASSWORD_DEFAULT)
            ]);

            JsonResponse::success([
                'user_id' => $userId,
            ], HttpStatusCode::CREATED);

        } catch (Exception $e) {
            error_log('Erro no registro: ' . $e->getMessage());
            JsonResponse::error( $e->getMessage(), HttpStatusCode::BAD_REQUEST);
        }
    }
    /**
     * {@inheritdoc}
     */
    public function login(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                JsonResponse::error('Método não permitido', HttpStatusCode::METHOD_NOT_ALLOWED);
                return;
            }

            $jsonInput = file_get_contents('php://input');
            $data = json_decode($jsonInput, true);
            
            if (!$data) {
                JsonResponse::error('Dados JSON inválidos ou vazios', HttpStatusCode::BAD_REQUEST);
                return;
            }

            $errors = $this->validateLoginData($data);
            
            if (!empty($errors)) {
                JsonResponse::validationError($errors);
                return;
            }

            $user = $this->userModel->findByEmail(trim($data['email']));

            if (!$user || !password_verify($data['password'], $user['password'])) {
                JsonResponse::error('Email ou senha inválidos', HttpStatusCode::UNAUTHORIZED);
                return;
            }

            $token = $this->jwtManager->generateToken([
                'user_id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name']
            ]);

            JsonResponse::success([
                'message' => 'Login realizado com sucesso',
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email']
                ]
            ]);

        } catch (Exception $e) {
            error_log('Erro no login: ' . $e->getMessage());
            JsonResponse::error('Erro interno do servidor: ' . $e->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * {@inheritdoc}
     */
    private function validateRegisterData($data): array
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors['name'] = 'Nome é obrigatório';
        } else {
            $name = trim($data['name']);
            if (strlen($name) < 2) {
                $errors['name'] = 'Nome deve ter pelo menos 2 caracteres';
            } elseif (strlen($name) > 100) {
                $errors['name'] = 'Nome deve ter no máximo 100 caracteres';
            }
        }

        if (empty($data['email'])) {
            $errors['email'] = 'Email é obrigatório';
        } else {
            $email = trim($data['email']);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Email inválido';
            } elseif (strlen($email) > 255) {
                $errors['email'] = 'Email deve ter no máximo 255 caracteres';
            }
        }

        if (empty($data['password'])) {
            $errors['password'] = 'Senha é obrigatória';
        } else {
            $password = $data['password'];
            if (strlen($password) < 6) {
                $errors['password'] = 'Senha deve ter pelo menos 6 caracteres';
            } elseif (strlen($password) > 255) {
                $errors['password'] = 'Senha deve ter no máximo 255 caracteres';
            }
        }

        return $errors;
    }
    /**
     * {@inheritdoc}
     */
    private function validateLoginData($data)
    {
        $errors = [];

        if (empty($data['email'])) {
            $errors['email'] = 'Email é obrigatório';
        } else {
            $email = trim($data['email']);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Email inválido';
            }
        }

        if (empty($data['password'])) {
            $errors['password'] = 'Senha é obrigatória';
        }

        return $errors;
    }
    /**
     * {@inheritdoc}
     */
    public function verifyToken(): void
    {
        try {
            $token = $this->jwtManager->getTokenFromHeader();

            if (!$token) {
                JsonResponse::unauthorized('Token não fornecido');
                return;
            }

            $userData = $this->jwtManager->validateToken($token);

            if (!$userData) {
                JsonResponse::unauthorized('Token inválido ou expirado');
                return;
            }

            JsonResponse::success([
                'message' => 'Token válido',
                'user' => $userData
            ]);

        } catch (Exception $e) {
            error_log('Erro na verificação do token: ' . $e->getMessage());
            JsonResponse::error('Erro interno do servidor', 500);
        }
    }
    /**
     * {@inheritdoc}
     */
    public function logout(): void
    {
        try {
            JsonResponse::success([
                'message' => 'Logout realizado com sucesso'
            ]);
        } catch (Exception $e) {
            error_log('Erro no logout: ' . $e->getMessage());
            JsonResponse::error('Erro interno do servidor', 500);
        }
    }
}