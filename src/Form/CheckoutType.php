<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\NumberType;
use App\Entity\Commande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints as Assert;

class CheckoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('etat', ChoiceType::class, [
            'choices' => [
                'Tunis' => 'Tunis',
                'Ariana' => 'Ariana',
                'Ben Arous' => 'Ben Arous',
                'Manouba' => 'Manouba',
                'Nabeul' => 'Nabeul',
                'Sousse' => 'Sousse',
                'Sfax' => 'Sfax',
            ],
            'attr' => ['class' => 'form-control small-dropdown'], // Ensure this matches your CSS
            'label' => 'Etat',
        ])
        
        
        
        
            ->add('rue', TextType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'e.g., 123 Sunshine Ave'],
                'label' => 'Street Address',
                'constraints' => [
                    new Assert\NotBlank(['message' => '🚨 Oops! Street address is required.']),
                    new Assert\Length([
                        'max' => 35,
                        'maxMessage' => '⚠️ Keep it short! Max 35 characters.'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-Z0-9\s]+$/',
                        'message' => '🚫 No special characters allowed!'
                    ]),
                ],
            ])
            ->add('ville', TextType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'e.g., Paris'],
                'label' => 'City',
                'constraints' => [
                    new Assert\NotBlank(['message' => '🏙️ You forgot to enter a city!']),
                    new Assert\Length([
                        'max' => 10,
                        'maxMessage' => '📏 Max 10 characters, please!'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-Z\s]+$/',
                        'message' => '🤨 Only letters allowed!'
                    ]),
                ],
            ])
            ->add('code_postal', NumberType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'e.g., 75001'],
                'label' => 'Postal Code',
                'html5' => true, // Enables numeric keyboard on mobile
                'constraints' => [
                    new Assert\NotBlank(['message' => '📬 Postal code is missing!']),
                    new Assert\Positive(['message' => '🔢 Postal code must be a positive number!']),
                    new Assert\Regex([
                        'pattern' => '/^\d{4,5}$/',
                        'message' => '🔢 Must be 4-5 digits only!',
                    ]),
                ],
            ])
            
            ->add('methodePaiment', ChoiceType::class, [
                'choices' => [
                    'Credit Card' => 'credit_card',
                    'PayPal' => 'paypal',
                    'Cash on Delivery' => 'cash'
                ],
                'attr' => ['class' => 'form-select'],
                'label' => 'Payment Method'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}
