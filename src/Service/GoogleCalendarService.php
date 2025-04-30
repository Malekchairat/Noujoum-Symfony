<?php

namespace App\Service;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Calendar\Event as GEvent;
use App\Entity\Evenement;

class GoogleCalendarService
{
    private Calendar $calendar;
    private string $calendarId;
    private string $tokenPath;

    public function __construct()
    {
        // Get project directory dynamically (works on any machine)
        $projectDir = dirname(__DIR__, 2); // Goes up two levels from src/Service
        
        // Use absolute paths for Windows
        $googleCredentials = $projectDir.'\config\google\credentials.json';
        $this->tokenPath = $projectDir.'\config\google\token.json';
        $this->calendarId = 'primary'; // Or use your specific calendar ID

        // Verify paths (debugging)
        if (!file_exists($googleCredentials)) {
            throw new \RuntimeException(sprintf(
                "Credentials file not found at: %s. Current working dir: %s",
                $googleCredentials,
                getcwd()
            ));
        }

        $client = new Client();
        $client->setAuthConfig($googleCredentials);
        $client->addScope(Calendar::CALENDAR);
        $client->setAccessType('offline'); // Important for refresh token
        $client->setPrompt('select_account consent');

        // Load existing token if available
        if (file_exists($this->tokenPath)) {
            $accessToken = json_decode(file_get_contents($this->tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // Refresh token if expired
        if ($client->isAccessTokenExpired()) {
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                $this->generateNewToken($client);
            }
            $this->saveToken($client->getAccessToken());
        }

        $this->calendar = new Calendar($client);
    }

    private function generateNewToken(Client $client): void
    {
        $client->setRedirectUri('http://localhost:8000/oauth2callback');
        $authUrl = $client->createAuthUrl();
        printf("Open this link in your browser:\n%s\n", $authUrl);
        exit;
    }

    private function saveToken(array $token): void
    {
        file_put_contents($this->tokenPath, json_encode($token));
    }

    public function createEvent(Evenement $evenement): string
    {
        $event = new GEvent([
            'summary'     => $evenement->getTitre(),
            'location'    => $evenement->getLieu(),
            'description' => $evenement->getDescription(),
            'start'       => ['dateTime' => $evenement->getDateDebut()->format(\DateTime::RFC3339)],
            'end'         => ['dateTime' => $evenement->getDateFin()->format(\DateTime::RFC3339)],
        ]);

        return $this->calendar->events->insert($this->calendarId, $event)->getId();
    }

    public function updateEvent(string $eventId, Evenement $evenement): void
    {
        $event = $this->calendar->events->get($this->calendarId, $eventId);
        $event->setSummary($evenement->getTitre());
        $event->setLocation($evenement->getLieu());
        $event->setDescription($evenement->getDescription());
        $event->setStart(new EventDateTime(['dateTime' => $evenement->getDateDebut()->format(\DateTime::RFC3339)]));
        $event->setEnd(new EventDateTime(['dateTime' => $evenement->getDateFin()->format(\DateTime::RFC3339)]));

        $this->calendar->events->update($this->calendarId, $eventId, $event);
    }

    public function deleteEvent(string $eventId): void
    {
        $this->calendar->events->delete($this->calendarId, $eventId);
    }
}