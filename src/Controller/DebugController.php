<?php 
// src/Controller/DebugController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DebugController extends AbstractController
{
    #[Route('/debug-google', name: 'debug_google')]
    public function debug(): Response
    {
        return new Response(sprintf(
            'Credentials path: %s<br>Exists: %s',
            $this->getParameter('google_credentials_path'),
            file_exists($this->getParameter('google_credentials_path')) ? 'YES' : 'NO'
        ));
    }
}