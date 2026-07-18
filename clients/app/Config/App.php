<?php

namespace App\Config;

final class App
{
    public const ROOT = __DIR__ . '/../..';

    public static function boot(): void
    {
        Env::load(self::ROOT);
        date_default_timezone_set((string) Env::get('APP_TIMEZONE', 'Asia/Kolkata'));

        if (session_status() !== PHP_SESSION_ACTIVE) {
            $lifetime = 60 * 60 * 24 * 30;
            ini_set('session.gc_maxlifetime', (string) $lifetime);
            session_name('uts_admin_session');
            session_set_cookie_params([
                'lifetime' => $lifetime,
                'httponly' => true,
                'samesite' => 'Lax',
                'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            ]);
            session_start();
        }
    }
}
