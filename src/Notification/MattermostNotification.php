<?php
declare(strict_types=1);

namespace Kanti\LetsencryptClient\Notification;

use Kanti\LetsencryptClient\Environment;
use function var_dump;

final class MattermostNotification extends AbstractNotification
{
    public function sendNotification(array $payload): void
    {
        $payload['username'] = $payload['username'] ?? 'Local HTTPS Companion';
        $payload['icon_emoji'] = $payload['icon_emoji'] ?? ':closed_lock_with_key:';
        $token = Environment::required('MATTERMOST_TOKEN');
        $url = Environment::required('MATTERMOST_URL');
        if (!$token || !$url) {
            return;
        }
        $this->send(sprintf('https://%s/hooks/%s', $url, $token), $payload, true);
    }
}
