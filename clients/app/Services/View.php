<?php

namespace App\Services;

final class View
{
    public static function render(string $template, array $data = [], string $layout = 'layout'): void
    {
        extract($data, EXTR_SKIP);
        $viewPath = __DIR__ . '/../Views/' . $template . '.php';
        ob_start();
        require $viewPath;
        $content = ob_get_clean();
        require __DIR__ . '/../Views/' . $layout . '.php';
    }

    public static function partial(string $template, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require __DIR__ . '/../Views/partials/' . $template . '.php';
    }
}
