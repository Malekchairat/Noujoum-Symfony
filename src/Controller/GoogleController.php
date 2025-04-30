<?php

namespace App\Controller;

use App\Service\GoogleClientService;
use Google\Service\Drive;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Google\Client;
use Symfony\Component\HttpFoundation\Request;



class GoogleController extends AbstractController
{
    #[Route('/google/drive', name: 'google_drive')]
    public function listFiles(GoogleClientService $googleClientService): Response
    {
        $client = $googleClientService->getClient();
        $service = new Drive($client);

        $files = $service->files->listFiles([
            'pageSize' => 10,
            'fields' => 'nextPageToken, files(id, name)',
        ]);

        $output = '';
        foreach ($files->getFiles() as $file) {
            $output .= sprintf("File: %s (%s)<br>", $file->getName(), $file->getId());
        }

        return new Response($output ?: 'No files found.');
    }


    #[Route('/oauth2callback', name: 'google_oauth_callback')]
    public function oauthCallback(Request $request): Response
    {
        $code = $request->query->get('code');

        $projectDir = $this->getParameter('kernel.project_dir');
        $credentialsPath = $projectDir.'/config/google/credentials.json';
        $tokenPath = $projectDir.'/config/google/token.json';

        $client = new Client();
        $client->setAuthConfig($credentialsPath);
        $client->addScope(\Google\Service\Calendar::CALENDAR);
        $client->setRedirectUri('http://localhost:8000/oauth2callback');

        $accessToken = $client->fetchAccessTokenWithAuthCode($code);
        file_put_contents($tokenPath, json_encode($accessToken));

        return new Response('Google access token saved successfully!');
    }

}
