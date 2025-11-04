<?php

namespace Core;

class Env
{
    private static array $variables = [];

    public static function load( $path = null): void
    {
        $path = $path ?? __DIR__ . '/../../.env';
        
        if (!file_exists($path)) {
            throw new \Exception("Arquivo .env não encontrado: $path");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue; // Ignora comentários
            }

            list($name, $value) = self::parseLine($line);
            if ($name !== null) {
                self::$variables[$name] = $value;
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }

    private static function parseLine( $line): array
    {
        if (strpos($line, '=') === false) {
            return [null, null];
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        // Remove aspas
        if (preg_match('/^"([\s\S]*)"$/', $value, $matches)) {
            $value = $matches[1];
        } elseif (preg_match('/^\'([\s\S]*)\'$/', $value, $matches)) {
            $value = $matches[1];
        }

        return [$name, $value];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$variables[$key] ?? $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }

    public static function isLocal(): bool
    {
        return self::get('APP_ENV') === 'local';
    }

    public static function isDebug(): bool
    {
        return self::get('APP_DEBUG') === 'true';
    }
}