<?php

// src/Form/UserType.php

namespace App\Form;

use App\Enum\RoleEnum;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nom', TextType::class, [
            'attr' => ['placeholder' => 'Nom'],
        ])
        ->add('prenom', TextType::class, [
            'attr' => ['placeholder' => 'Prenom'],
        ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'L\'email est requis.']),
                    new Email(['message' => 'Veuillez entrer une adresse email valide.']),
                ],
            ])
            ->add('mdp', PasswordType::class, [
                'mapped' => false,
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
                        'maxSize' => '2Mi',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                        'mimeTypesMessage' => 'Veuillez sélectionner une image valide (JPEG, PNG ou GIF)',
                    ]),
                ],
            ])
            ->add('role', ChoiceType::class, [
                'choices' => RoleEnum::cases(),
                'choice_label' => fn(RoleEnum $role) => match ($role) {
                    RoleEnum::ADMIN => 'ROLE_ADMIN',
                    RoleEnum::FAN => 'ROLE_USER',
                },
                'choice_value' => fn (?RoleEnum $role) => $role?->value,
                'expanded' => true,
                'multiple' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
