<?php

namespace App\Service;

use Google\Client as GoogleClient;

class GoogleClientService
{
    private $client;

    public function __construct()
    {
        $this->client = new GoogleClient();
        $this->client->setAuthConfig(__DIR__ . '/../../config/google_credentials.json'); // Ton fichier JSON ici
        $this->client->addScope([
            'https://www.googleapis.com/auth/drive', // ou tout autre scope
        ]);
    }

    public function getClient(): GoogleClient
    {
        return $this->client;
    }
}
