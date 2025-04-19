<?php
// src/Form/AdminReclamationType.php

namespace App\Form;

use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints as Assert;

class AdminReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Open' => 'OPEN',
                    'In Progress' => 'IN_PROGRESS',
                    'Resolved' => 'RESOLVED',
                    'Closed' => 'CLOSED'
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Choice([
                        'choices' => ['OPEN', 'IN_PROGRESS', 'RESOLVED', 'CLOSED'],
                        'message' => 'Invalid status value'
                    ])
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('answer', TextareaType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 500])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Enter official response...'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
            'csrf_protection' => false,
        ]);
    }
}