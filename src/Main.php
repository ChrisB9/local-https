<?php
declare(strict_types=1);

namespace Kanti\LetsencryptClient;

use Exception;
use Kanti\LetsencryptClient\Certificate\SelfSignedCertificate;
use Kanti\LetsencryptClient\Notification\NotificationManager;
use function var_dump;

final class Main
{
    private NginxProxy $nginxProxy;

    public function __construct(private array $argv)
    {
        $this->nginxProxy = new NginxProxy();
    }

    public function notify(): void
    {
        $mainDomain = Environment::required('HTTPS_MAIN_DOMAIN');
        $this->createIfNotExistsDefaultCertificate($mainDomain);

        $dataJsonReader = new DataJsonReader($mainDomain, 'var/data.json');
        $domains = $dataJsonReader->getDomains();
        if ((new CertChecker())->createIfNotExists($domains)) {
            $this->nginxProxy->restart();
        }
    }

    public function entrypoint(): void
    {
        $mainDomain = Environment::required('HTTPS_MAIN_DOMAIN');
        $this->createIfNotExistsDefaultCertificate($mainDomain);

        shell_exec('mkdir -p var');

        $argv = $this->argv;
        //first is /app/entrypoint.php
        array_shift($argv);
        passthru(implode(' ', $argv));
    }

    private function createIfNotExistsDefaultCertificate(string $mainDomain): void
    {
        $cert = new SelfSignedCertificate('/etc/nginx/certs/default');
        if ($cert->createIfNotExists()) {
            NotificationManager::deliver(
                sprintf(':selfie: CERTIFICATE self signed created %s.', $mainDomain),
                'localHttps',
            );
            $this->nginxProxy->restart();
        }
    }
}
