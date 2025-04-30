<?php
// src/Service/EmailService.php

namespace App\Service;

use App\Entity\Reclamation;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
Use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class EmailService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendReclamationNotification(string $to, string $subject, Reclamation $reclamation)
    {
        $email = (new TemplatedEmail())
            ->from('mahmoudtouil9@gmail.com')
            ->to($to)
            ->subject($subject)
            ->htmlTemplate('reclamation/reclamation_notification.html.twig')
            ->context([
                'reclamation' => $reclamation,
            ]);

        $this->mailer->send($email);
    }

}


