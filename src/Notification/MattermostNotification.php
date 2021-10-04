<?php
declare(strict_types=1);

namespace Kanti\LetsencryptClient\Notification;

use function getenv;

final class MattermostNotification extends AbstractNotification
{
    public function sendNotification(array $payload): void
    {
        $payload['username'] = $payload['username'] ?? 'Local HTTPS Companion';
        $payload['icon_emoji'] = $payload['icon_emoji'] ?? ':closed_lock_with_key:';
        $token = getenv('MATTERMOST_TOKEN');
        $url = getenv('MATTERMOST_URL');
        if (!$token || !$url) {
            return;
        }
        $this->send(sprintf('https://%s/hooks/%s', $url, $token), $payload);
    }
}
