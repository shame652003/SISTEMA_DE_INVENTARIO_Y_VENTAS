<?php

namespace App\Core;

class View
{
    public static function render(string $view, array $data = []): void
    {
        $viewPath = dirname(__DIR__) . '/views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            die("Vista no encontrada: $view");
        }

        extract($data);
        require $viewPath;
    }
}
