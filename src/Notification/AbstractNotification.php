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

    protected function send(string $url, array $payload, bool $prependWithPayload = false): void
    {
        $curl = curl_init();

        $data = json_encode($payload, JSON_THROW_ON_ERROR);
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $prependWithPayload ? ('payload=' . $data) : $data,
        ]);

        curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo 'cURL Error #:' . $err;
        }
    }
}
