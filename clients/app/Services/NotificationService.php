<?php

namespace App\Services;

use App\Config\Env;
use App\Models\ClientLead;
use App\Models\PushSubscription;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

final class NotificationService
{
    public function sendDueMeetingReminders(): int
    {
        $clients = new ClientLead();
        $count = 0;

        foreach ($clients->dueForNotification() as $client) {
            $this->sendEmail($client);
            $this->sendPush($client);
            $clients->markNotified((string) $client->_id);
            $count++;
        }

        return $count;
    }

    private function sendEmail(object $client): void
    {
        if (empty($client->email)) {
            return;
        }
        $meeting = $client->meeting_at ? $client->meeting_at->toDateTime()->setTimezone(new \DateTimeZone(date_default_timezone_get()))->format('d M Y, h:i A') : 'soon';
        $subject = 'Meeting reminder: ' . ($client->name ?? 'Client');
        $message = "Hello,\n\nThis is a reminder for your scheduled meeting with Unnat Technology Services at {$meeting}.\n\nThank you.";
        $from = Env::get('MAIL_FROM', 'notifications@example.com');
        $fromName = Env::get('MAIL_FROM_NAME', 'Unnat Technology Services');
        @mail((string) $client->email, $subject, $message, "From: {$fromName} <{$from}>");
    }

    private function sendPush(object $client): void
    {
        $publicKey = Env::get('VAPID_PUBLIC_KEY', '');
        $privateKey = Env::get('VAPID_PRIVATE_KEY', '');
        if (!$publicKey || !$privateKey || !class_exists(WebPush::class)) {
            return;
        }

        $webPush = new WebPush([
            'VAPID' => [
                'subject' => Env::get('VAPID_SUBJECT', 'mailto:admin@example.com'),
                'publicKey' => $publicKey,
                'privateKey' => $privateKey,
            ],
        ]);

        $payload = json_encode([
            'title' => 'Meeting in 30 minutes',
            'body' => 'Upcoming meeting with ' . ($client->name ?? 'a client') . '.',
            'url' => '/projects/' . ($client->project_id ?? '') . '/clients',
        ], JSON_UNESCAPED_SLASHES);

        foreach ((new PushSubscription())->all() as $record) {
            $subscription = json_decode(json_encode($record->subscription ?? []), true);
            if (!$subscription) {
                continue;
            }
            $webPush->queueNotification(Subscription::create($subscription), $payload);
        }

        foreach ($webPush->flush() as $report) {
            if (!$report->isSuccess()) {
                error_log('Push failed: ' . $report->getReason());
            }
        }
    }
}
