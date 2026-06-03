<?php
declare(strict_types=1);

abstract class Controller
{
    protected function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        $viewFile = VIEWS_PATH . '/' . $view . '.php';

        if (!file_exists($viewFile)) {
            throw new RuntimeException("Vue introuvable : $view");
        }

        require VIEWS_PATH . '/layouts/header.php';
        require $viewFile;
        require VIEWS_PATH . '/layouts/footer.php';
    }

    protected function redirect(string $path): never
    {
        header('Location: ' . APP_URL . $path);
        exit;
    }

    protected function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        exit;
    }

    protected function requireLogin(): void
    {
        if (!Session::isLoggedIn()) {
            Session::set('_redirect_after_login', $_SERVER['REQUEST_URI'] ?? '/');
            Session::flash('error', 'Veuillez vous connecter pour accéder à cette page.');
            $this->redirect('/login');
        }
    }

    protected function requireRole(string ...$roles): void
    {
        $this->requireLogin();

        $currentRole = Session::userRole();
        if ($currentRole === null || !in_array($currentRole, $roles, true)) {
            http_response_code(403);
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
    }
}
