<?php

declare(strict_types=1);

namespace Kanti\LetsencryptClient;

use function str_ends_with;
use function str_starts_with;

final class DataJsonReader
{
    public function __construct(private string $httpsMainDomain, private string $dataFilePath)
    {
    }

    public function getDomains(): array
    {
        $string = file_get_contents($this->dataFilePath);
        $domains = json_decode($string, true, 512, JSON_THROW_ON_ERROR);
        return $this->filterDomains($domains);
    }

    /**
     * @param string[] $domainArray
     * @return array
     */
    private function filterDomains(array $domainArray): array
    {
        $result = [];
        foreach (array_filter($domainArray) as $domain) {
            if (str_contains($domain, ',')) {
                $result = [...$result, ...$this->filterDomains(explode(',', $domain))];
            } elseif ($this->isValidDomain($domain)) {
                $result[] = $domain;
            }
        }
        return array_values(array_filter(array_unique($result)));
    }

    private function isValidDomain(string $domains): bool
    {
        // domains starting with ~ are regular expressions and not valid
        return !str_starts_with($domains, '~') && str_ends_with($domains, $this->httpsMainDomain);
    }
}
