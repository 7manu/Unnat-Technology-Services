<?php

namespace App\Controllers;

use App\Config\Env;
use App\Models\PushSubscription;
use App\Services\Response;

final class PushController
{
    public function vapid(): void
    {
        Response::json(['publicKey' => Env::get('VAPID_PUBLIC_KEY', '')]);
    }

    public function subscribe(): void
    {
        $payload = json_decode(file_get_contents('php://input') ?: '[]', true);
        if (!is_array($payload) || empty($payload['endpoint'])) {
            Response::json(['ok' => false, 'message' => 'Invalid subscription.'], 422);
        }
        (new PushSubscription())->save($payload);
        Response::json(['ok' => true]);
    }
}
