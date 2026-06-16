<?php

namespace App\Core;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Exception;

class JwtHandler
{
    private static ?array $config = null;

    private static function config(): array
    {
        if (self::$config === null) {
            self::$config = require dirname(__DIR__) . '/config/jwt.php';
        }
        return self::$config;
    }

    public static function generarAccessToken(array $usuario): string
    {
        $config = self::config();
        $ahora = time();

        $payload = [
            'iss'    => $config['issuer'],
            'iat'    => $ahora,
            'exp'    => $ahora + $config['access_ttl'],
            'sub'    => $usuario['cedula'],
            'data'   => [
                'cedula'    => $usuario['cedula'],
                'nombre'    => ($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? ''),
                'correo'    => $usuario['correo'] ?? '',
                'idRol'     => $usuario['idRol'] ?? 0,
                'nombreRol' => $usuario['nombreRol'] ?? '',
                'img'       => $usuario['img'] ?? '',
            ],
        ];

        return JWT::encode($payload, $config['secreto'], $config['algoritmo']);
    }

    public static function generarRefreshToken(array $usuario): string
    {
        $config = self::config();
        $ahora = time();
        $jti = bin2hex(random_bytes(16));

        $payload = [
            'iss'    => $config['issuer'],
            'iat'    => $ahora,
            'exp'    => $ahora + $config['refresh_ttl'],
            'sub'    => $usuario['cedula'],
            'jti'    => $jti,
            'type'   => 'refresh',
        ];

        return JWT::encode($payload, $config['secreto'], $config['algoritmo']);
    }

    public static function validarToken(string $token): object
    {
        $config = self::config();
        JWT::$leeway = 60; // 60 segundos de tolerancia para desfase de reloj
        return JWT::decode($token, new Key($config['secreto'], $config['algoritmo']));
    }

    public static function decodificarSinValidar(string $token): ?object
    {
        try {
            $config = self::config();
            $parts = explode('.', $token);
            if (count($parts) !== 3) return null;

            $payload = JWT::jsonDecode(JWT::urlsafeB64Decode($parts[1]));
            return $payload;
        } catch (Exception) {
            return null;
        }
    }
}
