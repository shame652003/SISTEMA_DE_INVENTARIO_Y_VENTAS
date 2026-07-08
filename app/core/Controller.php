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

    protected function datosModulo(string $modulo, string $jsFile = ''): array
    {
        $necesitaSelect2 = in_array($modulo, ['ventas', 'clientes', 'entradas', 'salidas', 'stock', 'reportes_pagos']);
        $necesitaDataTables = in_array($modulo, ['dashboard', 'usuarios', 'clientes', 'productos', 'entradas', 'salidas', 'stock', 'reportes_pagos']);

        $data = ['modulo' => $modulo];

        $extraCSS = '';
        $extraLibsJS = '';

        if ($necesitaSelect2) {
            $extraCSS .= '<link href="' . BASE_URL . '/assets/lib/select2/css/select2.min.css" rel="stylesheet"/>' . "\n";
            $extraLibsJS .= '<script src="' . BASE_URL . '/assets/lib/select2/js/select2.min.js"></script>' . "\n";
        }

        if ($necesitaDataTables) {
            $extraCSS .= '<link href="' . BASE_URL . '/assets/lib/datatables/css/dataTables.bootstrap5.min.css" rel="stylesheet">' . "\n";
            $extraCSS .= '<link href="' . BASE_URL . '/assets/lib/datatables/css/responsive.bootstrap5.min.css" rel="stylesheet">' . "\n";
            $extraLibsJS .= '<script src="' . BASE_URL . '/assets/lib/datatables/js/jquery.dataTables.min.js"></script>' . "\n";
            $extraLibsJS .= '<script src="' . BASE_URL . '/assets/lib/datatables/js/dataTables.bootstrap5.min.js"></script>' . "\n";
            $extraLibsJS .= '<script src="' . BASE_URL . '/assets/lib/datatables/js/dataTables.responsive.min.js"></script>' . "\n";
            $extraLibsJS .= '<script src="' . BASE_URL . '/assets/lib/datatables/js/responsive.bootstrap5.min.js"></script>' . "\n";
        }

        if ($extraCSS !== '') $data['extraCSS'] = $extraCSS;
        if ($extraLibsJS !== '') $data['extraLibsJS'] = $extraLibsJS;
        if ($jsFile !== '') $data['extraJS'] = '<script src="' . BASE_URL . '/assets/js/' . $jsFile . '"></script>';

        return $data;
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
