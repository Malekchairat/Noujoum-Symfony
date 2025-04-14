<?php

namespace App\Form;

use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du Produit'
            ])
            ->add('description', TextType::class, [
                'label' => 'Description'
            ])
            ->add('categorie', TextType::class, [
                'label' => 'Catégorie'
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix'
            ])
            ->add('disponibilite', NumberType::class, [
                'label' => 'Quantité disponible'
            ])
            ->add('image', FileType::class, [
                'label' => 'Image du produit',
                'mapped' => false, // Ne pas mapper directement à l'entité
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide',
                    ])
                ]
            ])
        
            ->add('submit', SubmitType::class, [
                'label' => 'Ajouter le produit'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
