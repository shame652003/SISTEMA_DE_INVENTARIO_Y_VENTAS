<?php

namespace App\Core;

class AuthMiddleware
{
    public static function verificar(): void
    {
        $token = self::extraerToken();

        if ($token === null) {
            self::denegar('Token no proporcionado', 'token_expirado');
            return;
        }

        try {
            $decoded = JwtHandler::validarToken($token);

            if (!isset($decoded->data)) {
                throw new \Exception('Token inválido');
            }

            $_REQUEST['auth'] = (array) $decoded->data;
        } catch (\Firebase\JWT\ExpiredException) {
            self::denegar('Token expirado', 'token_expirado');
        } catch (\Exception) {
            self::denegar('Token inválido');
        }
    }

    private static function denegar(string $mensaje, string $code = ''): void
    {
        $esAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'
               || ($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json'
               || ($_SERVER['CONTENT_TYPE'] ?? '') === 'application/json';

        if ($esAjax) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'mensaje' => $mensaje, 'code' => $code]);
            exit;
        }

        // Petición de página normal → redirigir al login
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        $loginUrl = $baseUrl . UrlCipher::encrypt('/login');
        $param = ($code === 'token_expirado') ? '?expired=1' : '';
        header('Location: ' . $loginUrl . $param);
        exit;
    }

    public static function opcional(): void
    {
        $token = self::extraerToken();
        if ($token === null) return;

        try {
            $decoded = JwtHandler::validarToken($token);
            if (isset($decoded->data)) {
                $_REQUEST['auth'] = (array) $decoded->data;
            }
        } catch (\Exception) {
            // Silencioso: usuario no autenticado pero puede continuar
        }
    }

    private static function extraerToken(): ?string
    {
        // 1. Cookie httpOnly: el navegador la envía automáticamente en cada request
        if (!empty($_COOKIE['jwt_token'])) {
            return $_COOKIE['jwt_token'];
        }

        // 2. Fallback: header Authorization (solo si no hay cookie)
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s+(.+)/i', $header, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public static function usuario(): ?array
    {
        return $_REQUEST['auth'] ?? null;
    }

    public static function cedula(): ?int
    {
        return isset($_REQUEST['auth']['cedula']) ? (int) $_REQUEST['auth']['cedula'] : null;
    }
}
