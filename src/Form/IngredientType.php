<?php

namespace App\Form;

use App\Entity\Ingredient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class IngredientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de l\'ingredient',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Tomate, Oignon, Farine...'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le nom de l\'ingredient est obligatoire'
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caracteres',
                        'maxMessage' => 'Le nom ne peut pas depasser {{ limit }} caracteres'
                    ])
                ]
            ])
            ->add('unite', ChoiceType::class, [
                'label' => 'Unite par defaut',
                'attr' => [
                    'class' => 'form-select'
                ],
                'choices' => [
                    'Gramme (g)' => 'g',
                    'Kilogramme (kg)' => 'kg',
                    'Millilitre (ml)' => 'ml',
                    'Centilitre (cl)' => 'cl',
                    'Litre (L)' => 'L',
                    'Piece' => 'piece',
                    'Cuillere a soupe' => 'c. a soupe',
                    'Cuillere a cafe' => 'c. a cafe',
                    'Pincee' => 'pincee',
                    'Tranche' => 'tranche',
                    'Gousse' => 'gousse',
                ],
                'placeholder' => 'Choisir une unite',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ingredient::class,
        ]);
    }
}