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

App\Config\App::boot();
$sent = (new App\Services\NotificationService())->sendDueMeetingReminders();
echo "Sent {$sent} meeting reminder batch(es)." . PHP_EOL;
