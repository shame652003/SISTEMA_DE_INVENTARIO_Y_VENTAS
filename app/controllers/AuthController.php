<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\JwtHandler;
use App\Core\AuthMiddleware;
use App\Models\Usuario;
use App\Models\RefreshToken;

class AuthController extends Controller
{
    public function login(): void
    {
        $cedula   = $_POST['cedula'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($cedula) || empty($password)) {
            $this->json(['ok' => false, 'mensaje' => 'Cédula y contraseña son obligatorios.'], 400);
            return;
        }

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->obtenerPorCedula((int) $cedula);

        if (!$usuario || !password_verify($password, $usuario['clave'])) {
            $this->json(['ok' => false, 'mensaje' => 'Credenciales inválidas.'], 401);
            return;
        }

        if ((int) $usuario['status'] !== 1) {
            $this->json(['ok' => false, 'mensaje' => 'Usuario inactivo. Contacte al administrador.'], 403);
            return;
        }

        $accessToken  = JwtHandler::generarAccessToken($usuario);
        $refreshToken = JwtHandler::generarRefreshToken($usuario);

        // Guardar hash del refresh token en BD
        $tokenHash = hash('sha256', $refreshToken);
        $configJwt = require dirname(__DIR__) . '/config/jwt.php';
        $expiresAt = date('Y-m-d H:i:s', time() + $configJwt['refresh_ttl']);

        $refreshModel = new RefreshToken();
        $refreshModel->eliminarPorUsuario((int) $usuario['cedula']);
        $refreshModel->guardar((int) $usuario['cedula'], $tokenHash, $expiresAt);

        // Cookie httpOnly: inmune a XSS, el navegador la envía automáticamente
        setcookie('jwt_token', $accessToken, [
            'expires'  => time() + $configJwt['access_ttl'],
            'path'     => '/',
            'secure'   => false,
            'httponly' => true,    // JavaScript NO puede leerla → seguro contra XSS
            'samesite' => 'Lax',
        ]);

        $this->json([
            'ok'            => true,
            'refresh_token' => $refreshToken,   // solo refresh token a JS
                'usuario'       => [
                    'cedula'    => $usuario['cedula'],
                    'nombre'    => trim($usuario['nombre'] . ' ' . ($usuario['apellido'] ?? '')),
                    'correo'    => $usuario['correo'],
                    'idRol'     => $usuario['idRol'],
                    'nombreRol' => $usuario['nombreRol'] ?? '',
                    'img'       => $usuario['img'] ?? '',
                ],
        ]);
    }

    public function refresh(): void
    {
        $refreshToken = $_POST['refresh_token'] ?? '';

        if (empty($refreshToken)) {
            $this->json(['ok' => false, 'mensaje' => 'Refresh token requerido.'], 400);
            return;
        }

        try {
            $decoded = JwtHandler::validarToken($refreshToken);

            if (!isset($decoded->type) || $decoded->type !== 'refresh') {
                $this->json(['ok' => false, 'mensaje' => 'Token inválido.'], 401);
                return;
            }

            $tokenHash = hash('sha256', $refreshToken);
            $refreshModel = new RefreshToken();
            $stored = $refreshModel->validar($tokenHash);

            if (!$stored) {
                // Token ya usado (rotación previa) o inexistente.
                // Solo revocamos TODOS los tokens si el JWT es válido pero no está en BD
                // (posible robo: alguien obtuvo un token que ya fue usado).
                // Si el token simplemente expiró en BD, no hacemos nada adicional.
                $this->json(['ok' => false, 'mensaje' => 'Token no encontrado o ya utilizado.'], 401);
                return;
            }

            // Rotación atómica: eliminar el viejo SOLO si aún existe
            $deleted = $refreshModel->eliminar($tokenHash);
            if (!$deleted) {
                // Otro request ya lo eliminó (concurrencia), rechazar
                $this->json(['ok' => false, 'mensaje' => 'Token ya utilizado.'], 401);
                return;
            }

            $usuarioModel = new Usuario();
            $usuario = $usuarioModel->obtenerPorCedula((int) $decoded->sub);

            if (!$usuario || (int) $usuario['status'] !== 1) {
                $this->json(['ok' => false, 'mensaje' => 'Usuario no encontrado o inactivo.'], 401);
                return;
            }

            $nuevoAccess  = JwtHandler::generarAccessToken($usuario);
            $nuevoRefresh = JwtHandler::generarRefreshToken($usuario);

            $nuevoHash = hash('sha256', $nuevoRefresh);
            $configJwt = require dirname(__DIR__) . '/config/jwt.php';
            $expiresAt = date('Y-m-d H:i:s', time() + $configJwt['refresh_ttl']);
            $refreshModel->guardar((int) $usuario['cedula'], $nuevoHash, $expiresAt);

            // Actualizar cookie httpOnly con el nuevo access token
            setcookie('jwt_token', $nuevoAccess, [
                'expires'  => time() + $configJwt['access_ttl'],
                'path'     => '/',
                'secure'   => false,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);

            $this->json([
                'ok'            => true,
                'refresh_token' => $nuevoRefresh,
            ]);
        } catch (\Firebase\JWT\ExpiredException) {
            // Limpiar tokens expirados del usuario
            $payload = JwtHandler::decodificarSinValidar($refreshToken);
            if ($payload && isset($payload->sub)) {
                $refreshModel = new RefreshToken();
                $refreshModel->eliminarPorUsuario((int) $payload->sub);
            }
            // Limpiar cookie para que no se reenvie el token expirado
            setcookie('jwt_token', '', [
                'expires'  => time() - 3600,
                'path'     => '/',
                'secure'   => false,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            $this->json(['ok' => false, 'mensaje' => 'Sesión expirada. Inicie sesión nuevamente.'], 401);
        } catch (\Throwable) {
            $this->json(['ok' => false, 'mensaje' => 'Token inválido.'], 401);
        }
    }

    public function logout(): void
    {
        AuthMiddleware::verificar();

        // Limpiar cookie httpOnly
        setcookie('jwt_token', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => false,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        $refreshToken = $_POST['refresh_token'] ?? '';

        if (!empty($refreshToken)) {
            $tokenHash = hash('sha256', $refreshToken);
            $refreshModel = new RefreshToken();
            $refreshModel->eliminar($tokenHash);
        }

        $this->json(['ok' => true, 'mensaje' => 'Sesión cerrada correctamente.']);
    }

    public function me(): void
    {
        AuthMiddleware::verificar();

        $usuario = AuthMiddleware::usuario();
        $this->json(['ok' => true, 'usuario' => $usuario]);
    }
}
