<?php

namespace App\Core;

use App\Core\AuthMiddleware;
use App\Core\Permisos;

class Controller
{
    protected function render(string $view, array $data = [], string $layout = 'default'): void
    {
        $viewPath = dirname(__DIR__) . '/views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            die("Vista no encontrada: $view");
        }

        extract($data);

        if ($layout === 'auth') {
            $esPublico = true;
            require dirname(__DIR__) . '/views/layouts/header.php';
            require $viewPath;
            require dirname(__DIR__) . '/views/layouts/footer.php';
        } elseif ($layout === 'app') {
            $esPublico = false;
            require dirname(__DIR__) . '/views/layouts/header.php';
            require dirname(__DIR__) . '/views/layouts/sidebar.php';
            echo '<main class="main-content">';
            require $viewPath;
            echo '</main>';
            require dirname(__DIR__) . '/views/layouts/footer.php';
        } else {
            $esPublico = true;
            require $viewPath;
        }
    }

    protected function auth(): ?array
    {
        return AuthMiddleware::usuario();
    }

    protected function verificarPermiso(string $modulo): bool
    {
        if (!Permisos::puede($modulo)) {
            $this->json(['ok' => false, 'mensaje' => 'No tienes permisos para acceder a este módulo.'], 403);
            return false;
        }
        return true;
    }

    protected function verificarPermisoVista(string $modulo): bool
    {
        if (!Permisos::puede($modulo)) {
            $baseUrl = defined('BASE_URL') ? BASE_URL : '';
            $dashboardUrl = $baseUrl . \App\Core\UrlCipher::encrypt('/dashboard');
            header("Location: $dashboardUrl");
            exit;
        }
        return true;
    }

    protected function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }
}
