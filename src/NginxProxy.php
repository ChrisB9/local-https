<?php

declare(strict_types=1);

namespace Kanti\LetsencryptClient;

use Exception;
use function sleep;
use function var_dump;

final class NginxProxy
{
    /** @var ?string */
    private ?string $dockerGenContainer = null;

    public function __construct()
    {
        $this->getDockerGenContainer();
    }

    public function restart(): void
    {
        $result = shell_exec(sprintf('docker restart %s', $this->getDockerGenContainer()));
        echo $result . PHP_EOL . 'Nginx Restarted.' . PHP_EOL;
    }

    private function getDockerGenContainer(): string
    {
        $label = 'com.github.kanti.local_https.nginx_proxy';
        if (getenv('CUSTOM_LABEL')) {
            $label = (string)getenv('CUSTOM_LABEL');
        }
        if ($this->dockerGenContainer === null) {
            sleep(5);
            $result = shell_exec(sprintf('docker ps -f "label=%s" -q', $label));
            if (!$result) {
                throw new Exception(sprintf(
                    'Error: nginx-proxy not found. Did you set the label=%s on nginx-proxy/nginx-proxy?',
                    $label,
                ));
            }
            $this->dockerGenContainer = trim($result);
        }
        return $this->dockerGenContainer;
    }
}
