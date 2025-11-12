<?php

namespace App\Form;

use App\Entity\Ingredient;
use App\Entity\RecetteIngredient;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecetteIngredientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ingredient', EntityType::class, [
                'class' => Ingredient::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisissez un ingrédient...',
                'attr' => [
                    'class' => 'ingredient-autocomplete-select'
                ]
            ])
            ->add('quantite', TextType::class, [
                'attr' => ['placeholder' => 'Ex: 250']
            ])
            ->add('unite', ChoiceType::class, [
                'choices' => [
                    'Unité(s)' => 'unité(s)',
                    'Gramme(s)' => 'g',
                    'Kilogramme(s)' => 'kg',
                    'Millilitre(s)' => 'ml',
                    'Litre(s)' => 'l',
                    'Cuillère(s) à café' => 'c.à.c',
                    'Cuillère(s) à soupe' => 'c.à.s',
                    'Tasse(s)' => 'tasse(s)',
                    'Pincée(s)' => 'pincée(s)'
                ],
                'placeholder' => 'Unité',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RecetteIngredient::class,
        ]);
    }
}