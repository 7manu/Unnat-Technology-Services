<?php

$root = dirname(__DIR__);
$vendor = $root . '/vendor/autoload.php';
if (is_file($vendor)) {
    require $vendor;
} else {
    spl_autoload_register(function (string $class) use ($root): void {
        $prefix = 'App\\';
        if (str_starts_with($class, $prefix)) {
            $path = $root . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (is_file($path)) {
                require $path;
            }
        }
    });
}

use App\Config\App;
use App\Config\Env;
use App\Controllers\AuthController;
use App\Controllers\ClientController;
use App\Controllers\ClientUserController;
use App\Controllers\ProjectController;
use App\Controllers\PushController;
use App\Controllers\SubadminController;
use App\Services\Auth;
use App\Services\Response;

App::boot();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

try {
    if ($path === '/' || $path === '/login') {
        $method === 'POST' ? (new AuthController())->login() : (new AuthController())->showLogin();
        exit;
    }

    if ($path === '/logout' && $method === 'POST') {
        (new AuthController())->logout();
    }

    if (!Auth::check()) {
        Response::redirect('/login');
    }

    if ($path === '/projects' && $method === 'GET') {
        (new ProjectController())->index();
    } elseif ($path === '/subadmins' && $method === 'GET') {
        (new SubadminController())->index();
    } elseif ($path === '/subadmins' && $method === 'POST') {
        (new SubadminController())->store();
    } elseif (preg_match('#^/subadmins/([a-f\d]{24})$#i', $path, $m) && $method === 'POST') {
        (new SubadminController())->update($m[1]);
    } elseif (preg_match('#^/subadmins/([a-f\d]{24})/delete$#i', $path, $m) && $method === 'POST') {
        (new SubadminController())->destroy($m[1]);
    } elseif ($path === '/client-users' && $method === 'GET') {
        (new ClientUserController())->index();
    } elseif ($path === '/client-users' && $method === 'POST') {
        (new ClientUserController())->store();
    } elseif (preg_match('#^/client-users/([a-f\d]{24})$#i', $path, $m) && $method === 'POST') {
        (new ClientUserController())->update($m[1]);
    } elseif (preg_match('#^/client-users/([a-f\d]{24})/delete$#i', $path, $m) && $method === 'POST') {
        (new ClientUserController())->destroy($m[1]);
    } elseif ($path === '/projects' && $method === 'POST') {
        (new ProjectController())->store();
    } elseif (preg_match('#^/projects/([a-f\d]{24})$#i', $path, $m) && $method === 'POST') {
        (new ProjectController())->update($m[1]);
    } elseif (preg_match('#^/projects/([a-f\d]{24})/delete$#i', $path, $m) && $method === 'POST') {
        (new ProjectController())->destroy($m[1]);
    } elseif (preg_match('#^/projects/([a-f\d]{24})/progress$#i', $path, $m) && $method === 'GET') {
        (new ProjectController())->progress($m[1]);
    } elseif (preg_match('#^/projects/([a-f\d]{24})/clients$#i', $path, $m) && $method === 'GET') {
        (new ClientController())->index($m[1]);
    } elseif (preg_match('#^/projects/([a-f\d]{24})/clients$#i', $path, $m) && $method === 'POST') {
        (new ClientController())->store($m[1]);
    } elseif (preg_match('#^/projects/([a-f\d]{24})/clients/([a-f\d]{24})$#i', $path, $m) && $method === 'POST') {
        (new ClientController())->update($m[1], $m[2]);
    } elseif (preg_match('#^/projects/([a-f\d]{24})/clients/([a-f\d]{24})/delete$#i', $path, $m) && $method === 'POST') {
        (new ClientController())->destroy($m[1], $m[2]);
    } elseif ($path === '/api/push/vapid-public-key' && $method === 'GET') {
        (new PushController())->vapid();
    } elseif ($path === '/api/push/subscribe' && $method === 'POST') {
        (new PushController())->subscribe();
    } else {
        http_response_code(404);
        echo 'Not found';
    }
} catch (Throwable $e) {
    http_response_code(500);
    $debug = filter_var(Env::get('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN);
    echo $debug ? htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') : 'Something went wrong.';
}
