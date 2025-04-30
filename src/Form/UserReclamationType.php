<?php
// src/Form/UserReclamationType.php

namespace App\Form;

use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints as Assert;

class UserReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Title',
                /*'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Title is required'
                    ]),
                    new Assert\Length([
                        'min' => 5,
                        'max' => 255,
                        'minMessage' => 'Title must be at least {{ limit }} characters',
                        'maxMessage' => 'Title cannot exceed {{ limit }} characters'
                    ])
                ],*/
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter a descriptive title'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                /*'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Description is required'
                    ]),
                    new Assert\Length([
                        'min' => 10,
                        'max' => 2000,
                        'minMessage' => 'Description must be at least {{ limit }} characters',
                        'maxMessage' => 'Description cannot exceed {{ limit }} characters'
                    ])
                ],*/
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 6,
                    'placeholder' => 'Describe your issue in detail...'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
            'csrf_protection' => false, // ⛔ CSRF protection is disabled
        ]);
    }
}