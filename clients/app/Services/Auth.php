<?php

namespace App\Services;

use App\Config\Env;
use App\Models\AdminUser;

final class Auth
{
    public static function check(): bool
    {
        return isset($_SESSION['admin']);
    }

    public static function user(): ?array
    {
        return $_SESSION['admin'] ?? null;
    }

    public static function attempt(string $email, string $password): bool
    {
        $email = strtolower(trim($email));
        $adminEmail = strtolower((string) Env::get('ADMIN_EMAIL', 'admin@example.com'));
        $storedHash = (string) Env::get('ADMIN_PASSWORD_HASH', '');

        $valid = false;
        if ($storedHash !== '') {
            $valid = password_verify($password, $storedHash);
        }

        if ($email === $adminEmail && $valid) {
            session_regenerate_id(true);
            $_SESSION['admin'] = [
                'email' => $adminEmail,
                'name' => 'Main Admin',
                'role' => 'admin',
                'project_ids' => [],
                'login_at' => time(),
            ];
            return true;
        }

        try {
            $subadmin = (new AdminUser())->findByEmail($email);
            if ($subadmin && password_verify($password, (string) ($subadmin->password_hash ?? ''))) {
                session_regenerate_id(true);
                $_SESSION['admin'] = [
                    'id' => (string) $subadmin->_id,
                    'email' => (string) $subadmin->email,
                    'name' => (string) ($subadmin->name ?? 'Subadmin'),
                    'role' => (string) ($subadmin->role ?? 'subadmin'),
                    'project_ids' => (array) ($subadmin->project_ids ?? []),
                    'login_at' => time(),
                ];
                return true;
            }
        } catch (\Throwable) {
            return false;
        }

        return false;
    }

    public static function isAdmin(): bool
    {
        return (self::user()['role'] ?? '') === 'admin';
    }

    public static function isClientUser(): bool
    {
        return (self::user()['role'] ?? '') === 'client';
    }

    public static function canManageClients(): bool
    {
        return in_array(self::user()['role'] ?? '', ['admin', 'subadmin'], true);
    }

    public static function canAccessProject(string $projectId): bool
    {
        if (self::isAdmin()) {
            return true;
        }
        return in_array($projectId, self::user()['project_ids'] ?? [], true);
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
}
