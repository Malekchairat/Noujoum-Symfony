<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FaceRecognitionController extends AbstractController
{
    #[Route('/recognize', name: 'app_face_recognition', methods: ['GET', 'POST'])]
    public function recognize(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $imageFile = $request->files->get('image');

            if ($imageFile) {
                // Move uploaded image to a safe location
                $uploadsDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads';
                $filename = uniqid() . '.' . $imageFile->guessExtension();
                $imagePath = $uploadsDirectory . '/' . $filename;
                $imageFile->move($uploadsDirectory, $filename);

                // Call the Python script
                $pythonScript = $this->getParameter('kernel.project_dir') . '/public/python_scripts/facial_recognition.py';
                $command = escapeshellcmd("python3 $pythonScript $imagePath");
                $output = shell_exec($command);

                return new Response("<h2>Result:</h2><pre>$output</pre>");
            }
        }

        return $this->render('face_recognition/index.html.twig');
    }
}
