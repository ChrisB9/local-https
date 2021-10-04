<?php
declare(strict_types=1);

namespace Kanti\LetsencryptClient\Notification;

final class SlackNotification extends AbstractNotification
{
    public function sendNotification(array $payload): void
    {
        $payload['username'] = $payload['username'] ?? 'Local HTTPS Companion';
        $payload['icon_emoji'] = $payload['icon_emoji'] ?? ':closed_lock_with_key:';
        $slackToken = getenv('SLACK_TOKEN');
        if (!$slackToken) {
            return;
        }
        $this->send('https://hooks.slack.com/services/' . $slackToken, $payload);
    }
}
