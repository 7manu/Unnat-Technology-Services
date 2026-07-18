<?php

$password = $argv[1] ?? '';
if ($password === '') {
    fwrite(STDERR, "Usage: php scripts/hash_admin_password.php \"your-password\"\n");
    exit(1);
}

echo password_hash($password, PASSWORD_DEFAULT) . PHP_EOL;
