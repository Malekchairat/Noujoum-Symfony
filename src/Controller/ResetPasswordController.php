<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Display & process form to request a password reset.
     */
    #[Route('', name: 'app_forgot_password_request')]
    public function request(Request $request, MailerInterface $mailer, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $email */
            $email = $form->get('email')->getData();

            return $this->processSendingPasswordResetEmail($email, $mailer, $translator);
        }

        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    /**
     * Confirmation page after a user has requested a password reset.
     */
    #[Route('/check-email', name: 'app_check_email')]
    public function checkEmail(): Response
    {
        // Generate a fake token if the user does not exist or someone hit this page directly.
        // This prevents exposing whether or not a user was found with the given email address or not
        if (null === ($resetToken = $this->getTokenObjectFromSession())) {
            $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
        }

        return $this->render('reset_password/check_email.html.twig', [
            'resetToken' => $resetToken,
        ]);
    }

    /**
     * Validates and process the reset URL that the user clicked in their email.
     */
    #[Route('/reset/{token}', name: 'app_reset_password')]
public function reset(
    Request $request, 
    UserPasswordHasherInterface $passwordHasher,
    TranslatorInterface $translator,
    string $token = null
): Response {
    if ($token) {
        $this->storeTokenInSession($token);
        return $this->redirectToRoute('app_reset_password');
    }

    $token = $this->getTokenFromSession();
    if (null === $token) {
        throw $this->createNotFoundException('No reset password token found.');
    }

    try {
        $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
    } catch (ResetPasswordExceptionInterface $e) {
        $this->addFlash('reset_password_error', sprintf(
            '%s - %s',
            $translator->trans(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_VALIDATE, [], 'ResetPasswordBundle'),
            $translator->trans($e->getReason(), [], 'ResetPasswordBundle')
        ));
        return $this->redirectToRoute('app_forgot_password_request');
    }

    $form = $this->createForm(ChangePasswordFormType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $this->resetPasswordHelper->removeResetRequest($token);
        
        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $form->get('plainPassword')->getData()
        );
        
        $user->setMdp($hashedPassword);
        $this->entityManager->flush();
        $this->cleanSessionAfterReset();

        $this->addFlash('success', 'Your password has been reset successfully!');
        return $this->redirectToRoute('app_login');
    }

    return $this->render('reset_password/reset.html.twig', [
        'resetForm' => $form->createView(),
        'token' => $token
    ]);
}
    /**
     * Process sending password reset email.
     */
    private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer, TranslatorInterface $translator): RedirectResponse
{
    $user = $this->entityManager->getRepository(User::class)->findOneBy([
        'email' => $emailFormData,
    ]);

    if (!$user) {
        $this->addFlash('info', 'If an account exists, you will receive an email.');
        return $this->redirectToRoute('app_check_email');
    }

    try {
        $resetToken = $this->resetPasswordHelper->generateResetToken($user);
    } catch (ResetPasswordExceptionInterface $e) {
        $this->addFlash('reset_password_error', 'There was a problem handling your password reset request');
        return $this->redirectToRoute('app_check_email');
    }

    $email = (new TemplatedEmail())
        ->from(new Address('jihen.troudi@esprit.tn', 'Password Reset'))
        ->to($user->getEmail())
        ->subject('Your password reset request')
        ->htmlTemplate('reset_password/email.html.twig')
        ->context([
            'resetToken' => $resetToken,
        ]);

    try {
        $mailer->send($email);
        $this->addFlash('success', 'Password reset email sent successfully!');
        $this->setTokenObjectInSession($resetToken);
    } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
        $this->addFlash('error', 'Failed to send email: '.$e->getMessage());
    }

    return $this->redirectToRoute('app_check_email');
}
#[Route('/test-email', name: 'app_test_email')]
public function testEmail(MailerInterface $mailer): Response
{
    $email = (new TemplatedEmail())
        ->from(new Address('jihen.troudi@esprit.tn', 'Test Email'))
        ->to('your@email.com') // Use your real email here
        ->subject('Test Email')
        ->text('This is a test email from Symfony');

    try {
        $mailer->send($email);
        return new Response('Email sent successfully! Check your inbox.');
    } catch (\Exception $e) {
        return new Response('Error sending email: '.$e->getMessage());
    }
}
}