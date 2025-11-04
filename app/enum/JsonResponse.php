<?php

namespace Enum;

use Enum\HttpStatusCode;

class JsonResponse
{
    public static function success($data = null, $code = 200, $message = 'Sucesso')
    {
        $statusCode = is_object($code) ? $code->value : $code;
        
        http_response_code($statusCode);
        header('Content-Type: application/json');
        
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
}