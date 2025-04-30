<?php
// src/Form/UserEditType.php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UserEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le nom ne peut pas être vide.']),
                ],
            ])
            ->add('prenom', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le prénom ne peut pas être vide.']),
                ],
            ])
            ->add('email', TextType::class, [  // Alternatively, you could use EmailType::class
                'constraints' => [
                    new NotBlank(['message' => 'L\'email ne peut pas être vide.']),
                    new Email(['message' => 'Veuillez entrer une adresse email valide.']),
                ],
            ])
            ->add('mdp', PasswordType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Mot de passe (laissez vide pour ne pas changer)',
                'constraints' => [
                    new Callback(function ($value, ExecutionContextInterface $context) {
                        if ($value && strlen($value) < 6) {
                            $context->buildViolation('Le mot de passe doit contenir au moins 6 caractères.')
                                ->addViolation();
                        }
                    }),
                ],
            ])
            ->add('tel', NumberType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le numéro de téléphone est requis.']),
                    new Regex([
                        'pattern' => '/^\d{8}$/',
                        'message' => 'Le numéro doit contenir exactement 8 chiffres.',
                    ]),
                ],
            ])
            ->add('image', FileType::class, [
                'label' => 'Image de profil',
                'mapped' => false,
                'required' => true, // Force to choose an image during edit
                'constraints' => [
                    new NotBlank(['message' => 'L\'image de profil est requise.']),
                    new File([
                        'maxSize' => '2Mi',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (jpeg, png, gif).',
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
