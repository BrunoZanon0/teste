<?php

namespace Middleware;

use Core\Logger;

class LoggingMiddleware
{
    private Logger $logger;

    public function __construct()
    {
        $this->logger = new Logger();
    }

    public function handle(): void
    {
        // Registra no início da requisição
        register_shutdown_function([$this, 'logRequest']);
    }

    public function logRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
        $route = $_SERVER['REQUEST_URI'] ?? '/';
        $statusCode = http_response_code() ?: 200;

        // Filtra dados sensíveis
        $filteredData = $this->filterSensitiveData([
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'unknown'
        ]);

        if ($statusCode >= 200 && $statusCode < 400) {
            $this->logger->logRequest($method, $route, $statusCode, $filteredData);
        } else {
            $this->logger->logError($method, $route, $statusCode, 'Erro na requisição', $filteredData);
        }
    }

    /**
     * Filtra dados sensíveis para não logar
     */
    private function filterSensitiveData(array $data): array
    {
        $sensitiveKeys = ['password', 'token', 'authorization', 'senha', 'credential'];
        
        return array_filter($data, function($value, $key) use ($sensitiveKeys) {
            foreach ($sensitiveKeys as $sensitive) {
                if (stripos($key, $sensitive) !== false) {
                    return false;
                }
            }
            return true;
        }, ARRAY_FILTER_USE_BOTH);
    }
}