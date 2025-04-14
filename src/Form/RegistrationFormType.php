<?php

// src/Form/RegistrationFormType.php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\File;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => ['placeholder' => 'Nom'],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est requis.']),
                ],
            ])
            ->add('prenom', TextType::class, [
                'attr' => ['placeholder' => 'Prénom'],
                'constraints' => [
                    new NotBlank(['message' => 'Le prénom est requis.']),
                ],
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'L\'email est requis.']),
                    new Email(['message' => 'Veuillez entrer une adresse email valide.']),
                ],
            ])
            ->add('mdp', PasswordType::class, [
                'mapped' => false, // This is important because the password is not directly mapped to the entity
                'constraints' => [
                    new NotBlank(['message' => 'Le mot de passe ne peut pas être vide.']),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                    ])
                ]
            ])
            ->add('tel', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le numéro de téléphone est requis.']),
                    new Regex([
                        'pattern' => '/^\d{8}$/',
                        'message' => 'Le numéro de téléphone doit contenir exactement 8 chiffres.',
                    ]),
                ],
            ])
            ->add('image', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                        'mimeTypesMessage' => 'Veuillez sélectionner une image valide (JPEG, PNG ou GIF)',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
