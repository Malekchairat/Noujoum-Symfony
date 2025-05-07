<?php
// src/Form/AdminCommandeType.php
namespace App\Form;

use App\Entity\Commande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

<<<<<<< HEAD
class AdminCommandeType extends AbstractType
=======
class AdminCommande extends AbstractType
>>>>>>> origin/GestionCommandes
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rue', TextType::class, [
                'label' => 'Rue',
                'attr' => ['placeholder' => 'Entrez l\'adresse'],
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'adresse ne peut pas être vide']),
                    new Assert\Length([
                        'max' => 30,
                        'maxMessage' => 'L\'adresse ne peut pas dépasser {{ limit }} caractères'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/',
                        'message' => 'L\'adresse ne peut contenir que des lettres'
                    ])
                ]
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
                'attr' => ['placeholder' => 'Entrez la ville'],
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La ville ne peut pas être vide']),
                    new Assert\Length([
                        'max' => 10,
                        'maxMessage' => 'La ville ne peut pas dépasser {{ limit }} caractères'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\s\-]+$/',
                        'message' => 'La ville ne peut contenir que des lettres'
                    ])
                ]
            ])
            ->add('codePostal', TextType::class, [
                'label' => 'Code Postal',
                'attr' => ['placeholder' => 'Entrez le code postal'],
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le code postal ne peut pas être vide']),
                    new Assert\Regex([
                        'pattern' => '/^\d{4,5}$/',
                        'message' => 'Le code postal doit être composé de 4 ou 5 chiffres'
                    ])
                ]
            ])
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
                'attr' => ['class' => 'form-select'],
                'label' => 'Etat',
                'required' => false,
            ])
            ->add('montantTotal', NumberType::class, [
                'label' => 'Montant Total (€)',
                'attr' => [
                    'placeholder' => 'Montant total de la commande',
                    'readonly' => true,
                    'class' => 'form-control bg-secondary'
                ],
                'required' => false,
            ])
            ->add('methodePaiment', ChoiceType::class, [
                'label' => 'Méthode de Paiement',
                'choices' => [
                    'Carte de crédit' => 'credit_card',
                    'PayPal' => 'paypal',
                    'Espèces' => 'cash',
                ],
                'required' => false,
            ])
            ->add('idUser', NumberType::class, [
                'label' => 'ID Utilisateur',
                'attr' => [
                    'placeholder' => 'Entrez l\'ID de l\'utilisateur',
                    'readonly' => true,
                    'class' => 'form-control bg-secondary'
                ],
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
            'csrf_protection' => true,
        ]);
    }
}