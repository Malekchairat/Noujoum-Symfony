<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
<<<<<<< HEAD
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
=======
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; // Add this line

>>>>>>> origin/GestionCommandes

class AuthController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
<<<<<<< HEAD
public function login(AuthenticationUtils $authenticationUtils): Response
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
=======
    public function login(AuthenticationUtils $authenticationUtils,UserPasswordHasherInterface $hasher,
    UserRepository $userRepo): Response
    {

        $testUser = $userRepo->findOneBy(['email' => 'known@email.com']);
    if ($testUser) {
        dump($hasher->isPasswordValid($testUser, 'actualpassword'));
    }
        // If user is already logged in, redirect them
        if ($this->getUser()) {
            return $this->redirectToRoute('app_homepage');
        }

        // Get login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // Last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // This will never be executed as Symfony will intercept this route
        throw new \LogicException('This method will be intercepted by the logout key on your firewall.');
    }

>>>>>>> origin/GestionCommandes
    #[Route('/check-email', name: 'check_email', methods: ['GET'])]
    public function checkEmail(Request $request, UserRepository $userRepository): JsonResponse
    {
        $email = $request->query->get('email');
        $user = $userRepository->findOneBy(['email' => $email]);

        return new JsonResponse(['exists' => $user !== null]);
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> origin/GestionCommandes
