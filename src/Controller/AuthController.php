<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
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

}
