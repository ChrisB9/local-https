<?php

declare(strict_types=1);

namespace Kanti\LetsencryptClient\Notification;

final class NotificationManager
{
    public static function deliver(string $text, string $username = '', string $channel = ''): void
    {
        $payload = [
            'text' => $text,
        ];
        if ($username) {
            $payload['username'] = $username;
        }
        if ($channel) {
            $payload['channel'] = $channel;
        }
        $type = getenv('NOTIFICATION_TYPE') ?: '';
        $class = match ($type) {
            'mattermost' => MattermostNotification::class,
            'slack' => SlackNotification::class,
            '' => null,
        };
        if (!$class) {
            return;
        }
        $notification = new $class;
        assert($notification instanceof AbstractNotification);
        $notification->sendNotification($payload);
    }
}
