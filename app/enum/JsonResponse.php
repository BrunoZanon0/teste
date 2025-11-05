<?php

namespace Enum;

use Enum\HttpStatusCode;
use Core\Logger;

class JsonResponse
{
    private static ?Logger $logger = null;

    private static function getLogger(): Logger
    {
        if (self::$logger === null) {
            self::$logger = new Logger();
        }
        return self::$logger;
    }

    public static function success($data = null, $code = 200, $message = 'Sucesso')
    {
        $statusCode = is_object($code) ? $code->value : $code;
        
        http_response_code($statusCode);
        header('Content-Type: application/json');

        // LOG DE SUCESSO
        self::logSuccess($statusCode, $message);

        $response = [
            'status' => 'success',
            'code' => $code,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function error($message, $code = HttpStatusCode::BAD_REQUEST, $details = [])
    {
        $statusCode = is_object($code) ? $code->value : $code;
        
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        
        // LOG DE ERRO
        self::logError($message, $statusCode, $details);
        
        $response = [
            'status' => 'error',
            'code' => $statusCode,
            'message' => $message
        ];
        
        if (!empty($details)) {
            $response['details'] = $details;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function validationError($errors, $message = 'Erros de validação')
    {
        http_response_code(422); // Nao da pra modificar o httpsRepsonses
        header('Content-Type: application/json');
        
        // LOG DE ERRO DE VALIDAÇÃO
        self::logValidationError($errors, $message);
        
        echo json_encode([
            'status' => 'error',
            'code' => HttpStatusCode::UNPROCESSABLE_ENTITY,
            'errors' => $errors
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function notFound($message = 'Recurso não encontrado')
    {
        self::error($message, HttpStatusCode::NOT_FOUND);
    }

    public static function unauthorized($message = 'Não autorizado')
    {
        self::error($message, HttpStatusCode::UNAUTHORIZED);
    }

    public static function forbidden($message = 'Acesso negado')
    {
        self::error($message, HttpStatusCode::FORBIDDEN);
    }

    public static function methodNotAllowed($message = 'Método não permitido')
    {
        self::error($message, HttpStatusCode::METHOD_NOT_ALLOWED);
    }

    /**
     * Métodos de logging (adicionados apenas)
     */
    private static function logSuccess(int $statusCode, string $message): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
        $route = $_SERVER['REQUEST_URI'] ?? '/';
        
        self::getLogger()->logRequest($method, $route, $statusCode, [
            'response_message' => $message
        ]);
    }

    private static function logError(string $message, int $code, array $details = []): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
        $route = $_SERVER['REQUEST_URI'] ?? '/';
        
        $context = [];
        if (!empty($details)) {
            $context['error_details'] = $details;
        }
        
        self::getLogger()->logError($method, $route, $code, $message, $context);
    }

    private static function logValidationError(array $errors, string $message): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
        $route = $_SERVER['REQUEST_URI'] ?? '/';
        
        self::getLogger()->logValidationError($method, $route, $errors);
    }
}