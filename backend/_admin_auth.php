<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('uts_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

function adminIsAuthenticated(): bool
{
    return ($_SESSION['uts_admin_authenticated'] ?? false) === true;
}

function requireAdmin(string $loginLocation = '../login.php'): void
{
    if (!adminIsAuthenticated()) {
        header('Location: ' . $loginLocation, true, 303);
        exit;
    }
}

function adminName(): string
{
    return (string) ($_SESSION['uts_admin_name'] ?? 'Administrator');
}

function adminId(): int
{
    return (int) ($_SESSION['uts_admin_id'] ?? 0);
}

function adminMobile(): string
{
    return (string) ($_SESSION['uts_admin_mobile'] ?? '');
}

function adminCsrfToken(): string
{
    if (empty($_SESSION['uts_admin_csrf'])) {
        $_SESSION['uts_admin_csrf'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['uts_admin_csrf'];
}

function verifyAdminCsrf(): bool
{
    $submitted = (string) ($_POST['csrf_token'] ?? '');
    $stored = (string) ($_SESSION['uts_admin_csrf'] ?? '');

    return $stored !== '' && hash_equals($stored, $submitted);
}
