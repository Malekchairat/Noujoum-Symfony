<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Http\SecurityEvents;
use App\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpClient\HttpClient;

class AuthController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        if ($this->getUser()) {
            $user = $this->getUser();

            if (in_array('ROLE_ADMIN', $user->getRoles())) {
                return $this->redirectToRoute('admin_dashboard');
            }

            return $this->redirectToRoute('app_homepage');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        // Verify reCAPTCHA if form was submitted
        if ($request->isMethod('POST')) {
            $recaptchaToken = $request->request->get('recaptchaToken');
            $secretKey = '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe';

            $client = HttpClient::create();
            $response = $client->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
                'body' => [
                    'secret' => $secretKey,
                    'response' => $recaptchaToken
                ]
            ]);

            $content = $response->toArray();
            if (!$content['success'] || $content['score'] < 0.5) {
                $this->addFlash('error', 'Failed CAPTCHA verification. Please try again.');
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Ce code ne sera jamais exécuté, car Symfony intercepte cette route.
        throw new \Exception('This should never be reached!');
    }

    #[Route('/check-email', name: 'check_email', methods: ['GET'])]
    public function checkEmail(Request $request, UserRepository $userRepository): JsonResponse
    {
        $email = $request->query->get('email');
        $user = $userRepository->findOneBy(['email' => $email]);

        return new JsonResponse(['exists' => $user !== null]);
    }

    #[Route('/face-login', name: 'face_login', methods: ['POST'])]
    public function faceLogin(
        Request $request,
        UserProviderInterface $userProvider,
        UserRepository $userRepository,
        EventDispatcherInterface $eventDispatcher,
        KernelInterface $kernel
    ): JsonResponse {
        $image = $request->files->get('image');
        if (!$image) {
            return new JsonResponse(['success' => false, 'message' => 'No image uploaded']);
        }

        $uploadsDir = $kernel->getProjectDir() . '/public/uploads/faces';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0777, true);
        }
        $filename = uniqid() . '.png';
        $imagePath = $uploadsDir . '/' . $filename;
        $image->move($uploadsDir, $filename);

        // Call Python script
        $pythonScript = $kernel->getProjectDir() . '/public/python_scripts/facial_recognition_login.py';
        $command = escapeshellcmd("python3 $pythonScript $imagePath");
        $output = shell_exec($command);

        if (!$output) {
            return new JsonResponse(['success' => false, 'message' => 'Face recognition failed']);
        }

        $recognizedEmail = trim($output);

        $user = $userRepository->findOneBy(['email' => $recognizedEmail]);

        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'Aucun utilisateur reconnu']);
        }

        // Authenticate the user manually
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->container->get('security.token_storage')->setToken($token);

        // Dispatch login event
        $event = new InteractiveLoginEvent($request, $token);
        $eventDispatcher->dispatch($event, SecurityEvents::INTERACTIVE_LOGIN);

        return new JsonResponse([
            'success' => true,
            'redirect' => $this->generateUrl('app_homepage') // redirect to home page
        ]);
    }

    #[Route('/facial-login-check', name: 'facial_login_check', methods: ['POST'])]
    public function facialLoginCheck(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['image'])) {
            return new JsonResponse(['success' => false], 400);
        }

        $imageData = $data['image'];

        // Save captured image to a temporary file
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $imagePath = $uploadDir . '/login_capture.jpg';
        $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
        $imageData = base64_decode($imageData);
        file_put_contents($imagePath, $imageData);

        // Launch Python script
        $process = new Process(['python3', $this->getParameter('kernel.project_dir') . '/python/recognize_face.py', $imagePath]);
        $process->run();

        // Check if Python script succeeded
        if (!$process->isSuccessful()) {
            return new JsonResponse(['success' => false]);
        }

        $output = trim($process->getOutput());

        if ($output === 'MATCH') {
            // Here you can manually log the user in if needed
            return new JsonResponse(['success' => true, 'redirect_url' => $this->generateUrl('app_homepage')]);
        } else {
            return new JsonResponse(['success' => false]);
        }
    }
    
}