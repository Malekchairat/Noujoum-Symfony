<?php
// src/Form/EvenementType.php
namespace App\Form;

use App\Entity\Evenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Titre', TextType::class, [
                'label'       => 'Titre',
                'required'    => true,
                'empty_data'  => '',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir le titre.',
                    ]),
                ],
            ])
            ->add('Description', TextareaType::class, [
                'label'       => 'Description',
                'required'    => true,
                'empty_data'  => '',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir la description.',
                    ]),
                ],
            ])
            ->add('dateDebut', DateTimeType::class, [
                'label'           => 'Date Début',
                'widget'          => 'single_text',
                'required'        => false,
                'empty_data'      => null,
                'invalid_message' => 'Veuillez saisir une date de début valide.',
                'constraints'     => [
                    new NotBlank([
                        'message' => 'Veuillez saisir la date de début.',
                    ]),
                    new GreaterThanOrEqual([
                        'value'   => new \DateTime(),
                        'message' => 'La date de début ne peut pas être dans le passé.',
                    ]),
                ],
            ])
            ->add('dateFin', DateTimeType::class, [
                'label'           => 'Date Fin',
                'widget'          => 'single_text',
                'required'        => false,
                'empty_data'      => null,
                'invalid_message' => 'Veuillez saisir une date de fin valide.',
                'constraints'     => [
                    new NotBlank([
                        'message' => 'Veuillez saisir la date de fin.',
                    ]),
                ],
            ])
            ->add('Lieu', TextType::class, [
                'label'       => 'Lieu',
                'required'    => true,
                'empty_data'  => '',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir le lieu.',
                    ]),
                ],
            ])
            ->add('Prix', NumberType::class, [
                'label'       => 'Prix',
                'required'    => true,
                'empty_data'  => 0,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir le prix.',
                    ]),
                    new GreaterThanOrEqual([
                        'value'   => 0,
                        'message' => 'Le prix ne peut pas être négatif.',
                    ]),
                ],
            ])
            ->add('TypeE', ChoiceType::class, [
                'label'       => 'Type',
                'required'    => true,
                'choices'     => [
                    'Fanmeet' => 'Fanmeet',
                    'Concert' => 'Concert'
                ],
                'placeholder' => 'Select an option',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner un type.',
                    ]),
                ],
            ])
            ->add('artiste', TextType::class, [
                'label'       => 'Artiste',
                'required'    => true,
                'empty_data'  => '',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir le nom de l\'artiste.',
                    ]),
                ],
            ])
            ->add('image', FileType::class, [
                'label'       => 'Image (Upload new file)',
                'mapped'      => false,
                'required'    => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez uploader une image.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
}