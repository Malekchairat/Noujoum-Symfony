<?php
// src/Service/SmsService.php

namespace App\Service;

use Twilio\Rest\Client;

class SmsService
{
    private string $sid;
    private string $token;
    private string $from;

    public function __construct(string $twilio_sid, string $twilio_token, string $twilio_from)
    {
        $this->sid = $twilio_sid;
        $this->token = $twilio_token;
        $this->from = $twilio_from;
    }

    public function sendSms(string $to, string $message): void
    {
        $client = new Client($this->sid, $this->token);

        $client->messages->create(
            $to,
            [
                'from' => $this->from,
                'body' => $message
            ]
        );
    }
}


