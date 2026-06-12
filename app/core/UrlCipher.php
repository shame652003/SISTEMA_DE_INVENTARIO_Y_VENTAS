<?php

namespace App\Core;

class UrlCipher
{
    private static string $cipher = 'AES-256-CBC';
    private static ?string $key = null;

    private static function getKey(): string
    {
        if (self::$key === null) {
            $config = require dirname(__DIR__) . '/config/jwt.php';
            self::$key = hash('sha256', $config['secreto'], true);
        }
        return self::$key;
    }

    public static function encrypt(string $url): string
    {
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($url, self::$cipher, self::getKey(), OPENSSL_RAW_DATA, $iv);
        // Nuevo formato: IV (16 bytes) + ciphertext, todo en base64url
        $encoded = rtrim(strtr(base64_encode($iv . $encrypted), '+/', '-_'), '=');
        return '/r/' . $encoded;
    }

    public static function decrypt(string $encryptedUrl): ?string
    {
        if (!str_starts_with($encryptedUrl, '/r/')) {
            return null;
        }
        $encoded = substr($encryptedUrl, 3);
        $base64 = strtr($encoded, '-_', '+/');
        $decoded = base64_decode($base64);
        if ($decoded === false) return null;

        // Intentar formato nuevo: IV (16 bytes) + ciphertext
        if (strlen($decoded) >= 16) {
            $iv = substr($decoded, 0, 16);
            $ciphertext = substr($decoded, 16);
            $result = openssl_decrypt($ciphertext, self::$cipher, self::getKey(), OPENSSL_RAW_DATA, $iv);
            if ($result !== false) return $result;
        }

        // Fallback: formato antiguo (IV fijo, openssl con salida base64 + doble encode)
        $oldIv = substr(hash('sha256', 'url_iv_fixed', true), 0, 16);
        $result = openssl_decrypt($decoded, self::$cipher, self::getKey(), 0, $oldIv);
        return $result !== false ? $result : null;
    }
}
