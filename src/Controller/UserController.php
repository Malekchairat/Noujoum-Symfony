<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\UserEditType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\UserRepository;  // Make sure this is the correct namespace for your UserRepository
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\FavorisRepository;

class UserController extends AbstractController
{
    private $userRepository;
    private $tokenStorage;

    public function __construct(UserRepository $userRepository, TokenStorageInterface $tokenStorage)
    {
        $this->userRepository = $userRepository;
        $this->tokenStorage = $tokenStorage;
    }

    #[Route('/user/create', name: 'user_create')]
    public function create(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $plainPassword = $form->get('mdp')->getData();
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setMdp($hashedPassword);

                /** @var UploadedFile $imageFile */
                $imageFile = $form->get('image')->getData();

                if ($imageFile) {
                    $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                    try {
                        $imageFile->move(
                            $this->getParameter('uploads_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                    }

                    $user->setImage($newFilename);
                }

                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Utilisateur créé avec succès !');
                return $this->redirectToRoute('user_list');
            } else {
                $this->addFlash('error', 'Veuillez corriger les erreurs du formulaire.');
            }
        }

        return $this->render('user/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/user/register', name: 'user_register')]
    public function register(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $plainPassword = $form->get('mdp')->getData();
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setMdp($hashedPassword);

                /** @var UploadedFile $imageFile */
                $imageFile = $form->get('image')->getData();

                if ($imageFile) {
                    $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                    try {
                        $imageFile->move(
                            $this->getParameter('uploads_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                    }

                    $user->setImage($newFilename);
                }

                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Utilisateur créé avec succès !');
                return $this->redirectToRoute('app_login');
            } else {
                $this->addFlash('error', 'Veuillez corriger les erreurs du formulaire.');
            }
        }

        return $this->render('user/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/user/{id}/edit', name: 'user_edit')]
    public function edit(Request $request, User $user, EntityManagerInterface $em)
{
    $form = $this->createForm(UserType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Save the user if the form is valid
        $em->flush();
        return $this->redirectToRoute('user_list');
    }

    // Render the form with error messages if not valid
    return $this->render('user/edit.html.twig', [
        'form' => $form->createView(),
    ]);
}


#[Route('/user/modifier', name: 'user_modifier')]
public function modifier(
    Request $request,
    EntityManagerInterface $em,
    UserPasswordHasherInterface $passwordHasher
): Response {
    $user = $this->getUser();

    if (!$user) {
        throw $this->createAccessDeniedException('Vous devez être connecté pour modifier votre profil.');
    }

    // If the user is an admin, redirect to the admin modification route
    if (in_array('ROLE_ADMIN', $user->getRoles())) {
        return $this->redirectToRoute('user_modifieradmin');
    }

    // Normal user form
    $form = $this->createForm(UserEditType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $imageFile = $form->get('image')->getData();

        if ($imageFile) {
            $newFilename = uniqid() . '.' . $imageFile->guessExtension();
            $imageFile->move($this->getParameter('uploads_directory'), $newFilename);
            $user->setImage($newFilename);
        }

        $em->flush();
        $this->addFlash('success', 'Compte modifié avec succès !');

        return $this->redirectToRoute('app_profile');
    }

    return $this->render('user/modifier.html.twig', [
        'form' => $form->createView(),
        'user' => $user,
    ]);
}


    #[Route('/user/modifieradmin', name: 'user_modifieradmin')]
public function modifieradmin(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
{
    $user = $this->getUser();
    if (!$user) {
        throw $this->createAccessDeniedException('Vous devez être connecté pour modifier votre profil.');
    }

    $originalPassword = $user->getMdp();
    $originalImage = $user->getImage();

    $form = $this->createForm(UserEditType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $newPassword = $form->get('mdp')->getData();
        if ($newPassword) {
            $hashed = $passwordHasher->hashPassword($user, $newPassword);
            $user->setMdp($hashed);
        } else {
            $user->setMdp($originalPassword);
        }

        /** @var UploadedFile $imageFile */
        $imageFile = $form->get('image')->getData();
        if ($imageFile) {
            $newFilename = uniqid() . '.' . $imageFile->guessExtension();
            $imageFile->move($this->getParameter('uploads_directory'), $newFilename);
            $user->setImage($newFilename);
        } else {
            $user->setImage($originalImage);
        }

        $em->flush();
        $this->addFlash('success', 'Compte modifié avec succès !');
        return $this->redirectToRoute('app_profile_admin');
    }

    return $this->render('user/modifieradmin.html.twig', [
        'form' => $form->createView(),
        'user' => $user,
    ]);
}


    #[Route('/user/show/{id}', name: 'user_show', requirements: ['id' => '\d+'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/users', name: 'user_list')]
public function listUsers(Request $request, EntityManagerInterface $em, PaginatorInterface $paginator): Response
{
    $query = $em->getRepository(User::class)->createQueryBuilder('u')->getQuery();

    $pagination = $paginator->paginate(
        $query, // Query or array
        $request->query->getInt('page', 1), // Current page number
        10 // Users per page
    );

    return $this->render('user/list.html.twig', [
        'users' => $pagination,
    ]);
}

#[Route('/delete-user/{id}', name: 'user_delete')]
public function deleteUser(
    int $id,
    EntityManagerInterface $em,
    TokenStorageInterface $tokenStorage,
    Request $request
): RedirectResponse {
    $userToDelete = $em->getRepository(User::class)->find($id);

    if (!$userToDelete) {
        throw $this->createNotFoundException('Utilisateur non trouvé.');
    }

    $currentUser = $this->getUser();

    // Check if the current user is deleting themselves
    $isSelf = $currentUser && $userToDelete->getIdUser() === $currentUser->getIdUser();

    // Remove user
    $em->remove($userToDelete);
    $em->flush();

    if ($isSelf) {
        // Manually log the user out
        $tokenStorage->setToken(null);
        $request->getSession()->invalidate();

        return $this->redirectToRoute('app_login');
    }

    return $this->redirectToRoute('user_list');
}

    
    #[Route('/delete-my-account', name: 'user_delete_self')]
    public function deleteMyAccount(Request $request, EntityManagerInterface $em, TokenStorageInterface $tokenStorage): RedirectResponse
    {
        $user = $this->getUser();
    
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour supprimer votre compte.');
        }
    
        // Manually log the user out before deleting
        $tokenStorage->setToken(null);
        $request->getSession()->invalidate();
    
        // Remove user
        $em->remove($user);
        $em->flush();
    
        // Redirect to login or homepage
        return $this->redirectToRoute('app_login');
    }
    // src/Controller/AdminController.php

// One of these methods is likely a duplicate
#[Route('/admin/user/{userId}/wishlist', name: 'admin_user_wishlist', methods: ['GET'])]
    public function viewUserWishlist(int $userId, UserRepository $userRepository, FavorisRepository $favorisRepository): Response
    {
        // Get the logged-in user
        $user = $this->getUser();

        // Fetch the user from the repository
        $targetUser = $userRepository->find($userId);

        if (!$targetUser) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        // Ensure the logged-in user is an admin or is the user themselves
        if (!$this->isGranted('ROLE_ADMIN') && $user !== $targetUser) {
            throw new AccessDeniedException('Vous n\'êtes pas autorisé à voir cette wishlist.');
        }

        // Get the wishlist items for this user
        $produits = $favorisRepository->findBy(['user' => $targetUser]);

        // Return the view with the user’s wishlist
        return $this->render('admin/user_wishlist.html.twig', [
            'user' => $targetUser,
            'produits' => $produits,
        ]);
    }

    
}
