<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class EmailService
{
    private $mailer;
    private $params;
    private $logger;

    public function __construct(
        MailerInterface $mailer, 
        ParameterBagInterface $params,
        LoggerInterface $logger
    ) {
        $this->mailer = $mailer;
        $this->params = $params;
        $this->logger = $logger;
    }

    public function sendOrderConfirmation(string $userEmail, array $orderDetails): array
    {

        $debugInfo = [
            'status' => 'attempting',
            'from' => 'hedifridhy@gmail.com',
            'to' => $userEmail, // This should show the user's email
            'subject' => 'Confirmation de commande',
        ];
        try {
            $this->logger->info('Starting email sending process', [
                'order_details' => $orderDetails,
                'mailer_dsn' => $_ENV['MAILER_DSN'] ?? 'not set'
            ]);
    
            $email = (new Email())
                ->from('hedifridhy@gmail.com')  // Keep this as sender
                ->to($userEmail)  // Changed to use the user's email
                ->subject('Confirmation de votre commande - Noujoum Shop')  // Updated subject
                ->html($this->getOrderConfirmationTemplate($orderDetails));


            $this->logger->info('Attempting to send email', $debugInfo);

            try {
                $this->mailer->send($email);
                $this->logger->info('Email sent successfully');
                $debugInfo['status'] = 'success';
                return $debugInfo;
            } catch (TransportExceptionInterface $e) {
                $this->logger->error('Transport error while sending email', [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'debug' => $e->getDebug()
                ]);
                throw $e;
            }
            
        } catch (\Exception $e) {
            $errorInfo = [
                'status' => 'error',
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
                'mailer_dsn' => $_ENV['MAILER_DSN'] ?? 'not set'
            ];
            
            $this->logger->error('Failed to send email', $errorInfo);
            return $errorInfo;
        }
    }

    private function getOrderConfirmationTemplate(array $orderDetails): string
    {
        $products = $orderDetails['products_summary'] ?? '';
        $total = $orderDetails['montant_total'] ?? 0;
        $address = $orderDetails['rue'] ?? '';
        $city = $orderDetails['ville'] ?? '';
        $postalCode = $orderDetails['code_postal'] ?? '';

        return "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8f9fa;'>
                <div style='background-color: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                    <h1 style='color: #333; text-align: center; margin-bottom: 30px;'>Nouvelle commande reçue!</h1>
                    
                    <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>
                        <h2 style='color: #444; margin-bottom: 15px;'>Détails de la commande</h2>
                        <p style='margin: 10px 0;'><strong>Produits:</strong><br>{$products}</p>
                        <p style='margin: 10px 0;'><strong>Montant total:</strong> {$total}€</p>
                    </div>

                    <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>
                        <h2 style='color: #444; margin-bottom: 15px;'>Adresse de livraison</h2>
                        <p style='margin: 10px 0;'>{$address}<br>{$postalCode} {$city}</p>
                    </div>

                    <div style='text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;'>
                        <p style='color: #666; margin: 5px 0;'>Cette commande nécessite votre attention.</p>
                        <p style='color: #333; font-weight: bold; margin: 5px 0;'>L'équipe Noujoum</p>
                    </div>
                </div>
            </div>
        ";
    }
}