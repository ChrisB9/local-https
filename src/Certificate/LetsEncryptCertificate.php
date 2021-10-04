<?php

declare(strict_types=1);

namespace Kanti\LetsencryptClient\Certificate;

use JetBrains\PhpStorm\ArrayShape;
use Kanti\LetsencryptClient\Environment;
use RuntimeException;

use function strtotime;

final class LetsEncryptCertificate
{
    /** @var string[] */
    public array $newlyCreatedDomains = [];

    public function __construct(private array $domains)
    {
        sort($this->domains);
        $domain = Environment::required('HTTPS_MAIN_DOMAIN');
        $domainList = sprintf(
            '-d %s -d %s',
            $domain,
            implode(' -d ', $this->domains),
        );
        $dns = Environment::required('DNS_CLIENT');
        $requiredUpdate = $this->isUpdateRequired();
        if ($requiredUpdate === false) {
            echo 'No update for certificate needed' . PHP_EOL;
            return;
        }

        $result = shell_exec(sprintf(
            'acme.sh %s --server letsencrypt --dns dns_%s %s --fullchain-file %s.crt --key-file %s.key',
            $requiredUpdate ? '--renew --force' : '--issue',
            $dns,
            $domainList,
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

    #[ArrayShape(['updated' => "string[]", 'certificates' => "array"])]
    public static function fromDomainList(array $domains): array
    {
        return [
            'updated' => (new LetsEncryptCertificate($domains))->newlyCreatedDomains,
            'certificates' => self::getCurrentCertificateInformation(),
        ];
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

    private function isUpdateRequired(): ?bool
    {
        $certificates = self::getCurrentCertificateInformation();
        $mainDomain = Environment::required('HTTPS_MAIN_DOMAIN');
        foreach ($certificates as $certificate) {
            if ($mainDomain !== $certificate['Main_Domain']) {
                continue;
            }
            if (implode(',', $this->domains) !== $certificate['SAN_Domains']) {
                $this->newlyCreatedDomains = array_diff($this->domains, explode(',', $certificate['SAN_Domains']));
                return null;
            }
            if (strtotime('now + 1 week') >= strtotime($certificate['Renew'])) {
                return true;
            }
            return false;
        }
        return null;
    }
}
