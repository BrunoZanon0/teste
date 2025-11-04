<?php

namespace Enum;

use Enum\HttpStatusCode;

class JsonResponse
{
    public static function send(
        HttpStatusCode $status,
        array $data = [],
        array $headers = []
    ): void {
        http_response_code($status->value);

        header('Content-Type: application/json; charset=utf-8');
        
        foreach ($headers as $header => $value) {
            header("$header: " . (string)$value);
        }

        // Monta a resposta
        $response = [
            'status' => $status->isSuccess() ? 'success' : 'error',
            'code' => $status->value,
            'message' => $status->getMessage()];

        echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        exit;
    }

    public static function success(
        array $data = [],
        HttpStatusCode $status = HttpStatusCode::OK
    ): void {
        self::send($status, $data);
    }

    public static function error(
         $message = null,
        HttpStatusCode $status = HttpStatusCode::BAD_REQUEST,
        array $details = []
    ): void {
        $data = [];
        
        if ($message) {
            $data['message'] = $message;
        }
        
        if (!empty($details)) {
            $data['details'] = $details;
        }
        
        self::send($status, $data);
    }

    public static function notFound(): void
    {
        self::send(HttpStatusCode::NOT_FOUND);
    }
}