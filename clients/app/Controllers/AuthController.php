<?php

namespace App\Controllers;

use App\Services\Auth;
use App\Services\Csrf;
use App\Services\Response;
use App\Services\View;

final class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            Response::redirect('/projects');
        }
        View::render('login', ['title' => 'Admin Login', 'error' => $_SESSION['flash_error'] ?? null], 'auth_layout');
        unset($_SESSION['flash_error']);
    }

    public function login(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            $_SESSION['flash_error'] = 'Security token expired. Please try again.';
            Response::redirect('/login');
        }

        $email = trim($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        if ($email === '' || $password === '' || !Auth::attempt($email, $password)) {
            $_SESSION['flash_error'] = 'Invalid admin email or password.';
            Response::redirect('/login');
        }

        Response::redirect('/projects');
    }

    public function logout(): void
    {
        Auth::logout();
        Response::redirect('/login');
    }
}
