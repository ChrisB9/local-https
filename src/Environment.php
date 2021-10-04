<?php
declare(strict_types=1);

namespace Kanti\LetsencryptClient;


final class Environment
{
    public static function required(string $key): string
    {
        $env = getenv($key) ?: '';
        if (!$env) {
            throw new \Exception(sprintf('environment variable %s must be set.', $key));
        }
        return $env;
    }
}
