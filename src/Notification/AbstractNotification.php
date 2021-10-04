<?php
declare(strict_types=1);

namespace Kanti\LetsencryptClient\Notification;


use function curl_close;
use function curl_error;
use function curl_exec;
use function curl_init;
use function curl_setopt_array;
use function json_encode;
use const CURLOPT_CUSTOMREQUEST;
use const CURLOPT_POSTFIELDS;
use const CURLOPT_URL;

abstract class AbstractNotification
{
    abstract public function sendNotification(array $payload);

    protected function send(string $url, array $payload): void
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo 'cURL Error #:' . $err;
        }
    }
}
