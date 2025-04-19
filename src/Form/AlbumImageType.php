<?php

namespace App\Form;

use App\Entity\AlbumImage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AlbumImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('imagePath', FileType::class, [
                'label' => 'Image de l\'album',
                'mapped' => false,  // Nous ne lierons pas directement ce champ à l'entité, car il s'agit d'un fichier
                'required' => true,
                'attr' => ['accept' => 'image/*'],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Ajouter l\'image',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AlbumImage::class,
        ]);
    }
}
