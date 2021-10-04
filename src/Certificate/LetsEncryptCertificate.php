<?php

declare(strict_types=1);

namespace Kanti\LetsencryptClient\Certificate;

use Kanti\LetsencryptClient\Environment;
use RuntimeException;

final class LetsEncryptCertificate
{
    public function __construct(private array $domains)
    {
        sort($this->domains);
        $domain = Environment::required('HTTPS_MAIN_DOMAIN');
        $domains = sprintf(
            '-d %s -d %s',
            $domain,
            implode(' -d ', $this->domains),
        );
        $dns = Environment::required('DNS_CLIENT');

        $result = shell_exec(sprintf(
            'acme.sh --issue --server letsencrypt --dns dns_%s %s --fullchain-file %s.crt --key-file %s.key',
            $dns,
            $domains,
            '/etc/nginx/certs/' . $domain,
            '/etc/nginx/certs/' . $domain,
        ));
        if (!$result) {
            throw new RuntimeException(sprintf(
                'This should never happen, cert got not created? domains: %s',
                implode(',', $this->domains))
            );
        }
    }

    public static function fromDomainList(array $domains): array
    {
        new LetsEncryptCertificate($domains);
        return self::getCurrentCertificateInformation();
    }

    private static function getCurrentCertificateInformation(): array
    {
        $result = shell_exec('acme.sh --list --listraw');
        $lines = array_filter(explode(PHP_EOL, $result));
        $head = explode('|', array_shift($lines));
        $data = [];
        foreach ($lines as $certificates) {
            $data[] = array_combine($head, explode('|', $certificates));
        }
        return $data;
    }
}
