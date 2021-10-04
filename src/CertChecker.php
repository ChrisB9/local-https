<?php
declare(strict_types=1);

namespace Kanti\LetsencryptClient;

use Kanti\LetsencryptClient\Certificate\LetsEncryptCertificate;
use Kanti\LetsencryptClient\Notification\NotificationManager;

final class CertChecker
{
    public static function createIfNotExists(array $domains): bool
    {
        $certs = LetsEncryptCertificate::fromDomainList($domains);
        var_dump($certs['certificates']);
        $updated = count($certs['updated']) > 0;
        if ($updated) {
            NotificationManager::deliver('Updated Domains: ' . implode(', ', $certs['updated']));
        }
        return $updated;
    }
}
