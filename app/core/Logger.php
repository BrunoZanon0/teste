<?php

namespace Core;

use DateTime;
use Exception;

class Logger
{
    private string $logPath;
    private string $logFile;

    public function __construct(string $logName = 'api')
    {
        $this->logPath = __DIR__ . '/../../storage/logs/';
        $this->ensureLogDirectoryExists();
        $this->logFile = $this->logPath . $logName . '_' . date('Y-m-d') . '.log';
    }

    /**
     * Garante que o diretório de logs existe
     */
    private function ensureLogDirectoryExists(): void
    {
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }

    /**
     * Registra uma requisição com sucesso
     */
    public function logRequest(string $method, string $route, int $statusCode, array $data = []): void
    {
        $logEntry = [
            'timestamp' => $this->getCurrentTimestamp(),
            'type' => 'REQUEST',
            'method' => strtoupper($method),
            'route' => $route,
            'status' => 'SUCCESS',
            'status_code' => $statusCode,
            'data' => $data
        ];

        $this->writeLog($logEntry);
    }

    /**
     * Registra um erro
     */
    public function logError(string $method, string $route, int $statusCode, string $errorMessage, array $context = []): void
    {
        $logEntry = [
            'timestamp' => $this->getCurrentTimestamp(),
            'type' => 'ERROR',
            'method' => strtoupper($method),
            'route' => $route,
            'status' => 'ERROR',
            'status_code' => $statusCode,
            'message' => $errorMessage,
            'context' => $context
        ];

        $this->writeLog($logEntry);
    }

    /**
     * Registra uma validação com erro
     */
    public function logValidationError(string $method, string $route, array $validationErrors): void
    {
        $logEntry = [
            'timestamp' => $this->getCurrentTimestamp(),
            'type' => 'VALIDATION_ERROR',
            'method' => strtoupper($method),
            'route' => $route,
            'status' => 'ERROR',
            'status_code' => 422,
            'validation_errors' => $validationErrors
        ];

        $this->writeLog($logEntry);
    }

    /**
     * Registra autenticação
     */
    public function logAuth(string $method, string $route, string $action,  $userId = null): void
    {
        $logEntry = [
            'timestamp' => $this->getCurrentTimestamp(),
            'type' => 'AUTH',
            'method' => strtoupper($method),
            'route' => $route,
            'action' => $action,
            'user_id' => $userId,
            'status' => 'SUCCESS'
        ];

        $this->writeLog($logEntry);
    }

    /**
     * Registra autenticação com erro
     */
    public function logAuthError(string $method, string $route, string $action, string $error): void
    {
        $logEntry = [
            'timestamp' => $this->getCurrentTimestamp(),
            'type' => 'AUTH_ERROR',
            'method' => strtoupper($method),
            'route' => $route,
            'action' => $action,
            'status' => 'ERROR',
            'error' => $error
        ];

        $this->writeLog($logEntry);
    }

    /**
     * Escreve no arquivo de log
     */
    private function writeLog(array $logEntry): void
    {
        try {
            $logLine = json_encode($logEntry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
            file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            // Fallback para não quebrar a aplicação se o log falhar
            error_log('Falha ao escrever log: ' . $e->getMessage());
        }
    }

    /**
     * Retorna timestamp atual formatado
     */
    private function getCurrentTimestamp(): string
    {
        return (new DateTime())->format('Y-m-d H:i:s');
    }

    /**
     * Limpa logs antigos (mais de 30 dias)
     */
    public function cleanupOldLogs(int $days = 30): void
    {
        try {
            $files = glob($this->logPath . '*.log');
            $cutoff = time() - ($days * 24 * 60 * 60);

            foreach ($files as $file) {
                if (filemtime($file) < $cutoff) {
                    unlink($file);
                }
            }
        } catch (Exception $e) {
            error_log('Falha ao limpar logs antigos: ' . $e->getMessage());
        }
    }
}