<?php

namespace App\Core;

class Permisos
{
    private static array $modulosSuper = [
        'dashboard', 'usuarios', 'perfil', 'bitacora', 'ayuda',
        'clientes', 'productos', 'entradas', 'salidas', 'ventas', 'stock',
        'reportes_pagos', 'reportes_generales'
    ];

    private static array $modulosAdmin = [
        'dashboard', 'perfil', 'bitacora', 'ayuda',
        'clientes', 'productos', 'entradas', 'salidas', 'ventas', 'stock',
        'reportes_pagos', 'reportes_generales'
    ];

    private static array $modulosDefault = [
        'dashboard', 'perfil', 'ayuda',
        'clientes', 'ventas', 'stock', 'reportes_pagos'
    ];

    public static function puede(string $modulo): bool
    {
        $usuario = AuthMiddleware::usuario();
        if (!$usuario) return false;

        $rol = $usuario['nombreRol'] ?? '';

        if ($rol === 'Super Usuario') {
            return in_array($modulo, self::$modulosSuper);
        }

        if ($rol === 'Administrador') {
            return in_array($modulo, self::$modulosAdmin);
        }

        return in_array($modulo, self::$modulosDefault);
    }

    public static function esSuper(): bool
    {
        $usuario = AuthMiddleware::usuario();
        if (!$usuario) return false;

        return ($usuario['nombreRol'] ?? '') === 'Super Usuario';
    }
}
