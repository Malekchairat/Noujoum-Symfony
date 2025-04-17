<?php

namespace App\Form;

use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id')
            ->add('titre')
            ->add('description')
            ->add('date_creation',DateType::class, [
                   'widget' => 'single_text',  // Use a single text input for the date
                   'format' => 'yyyy-MM-dd',   // You can change the format as per your requirement
        ])
            ->add('statut')
            ->add('priorite')
            ->add('user_id')
            ->add('answer')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
        ]);
    }
}