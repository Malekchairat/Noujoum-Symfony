<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function profile(): Response
{
    $user = $this->getUser(); // Make sure you are properly fetching the logged-in user

    if (!$user) {
        return $this->redirectToRoute('app_login'); // Ensure redirection if no user is logged in
    }

    return $this->render('user/profile.html.twig', [
        'user' => $user,
        'error' => null,
    ]);
}

    

    #[Route('/profileadmin', name: 'app_profile_admin')]
    public function profileadmin(UserRepository $userRepository): Response
    {
        $currentUser = $this->getUser();

        if (!$currentUser) {
            return $this->render('user/profileadmin.html.twig', [
                'error' => 'Utilisateur non connecté.',
                'user' => null,
            ]);
        }

        $user = $userRepository->findOneBy(['email' => $currentUser->getUserIdentifier()]);

        if (!$user) {
            return $this->render('user/profileadmin.html.twig', [
                'error' => 'Utilisateur introuvable.',
                'user' => null,
            ]);
        }

        return $this->render('user/profileadmin.html.twig', [
            'user' => $user,
            'error' => null,
        ]);
    }
}
