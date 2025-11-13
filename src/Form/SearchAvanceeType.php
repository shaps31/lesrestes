<?php

namespace App\Form;

use App\Entity\Categorie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchAdvancedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('query', TextType::class, [
                'label' => 'Mots-clés (nom ou ingrédient)',
                'required' => false,
                'attr' => ['placeholder' => 'ex: pizza, tomates...']
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'placeholder' => 'Toutes les catégories',
                'required' => false,
            ])
            ->add('difficulte', ChoiceType::class, [
                'choices' => [
                    'Facile' => 1,
                    'Moyen' => 2,
                    'Difficile' => 3,
                ],
                'placeholder' => 'Toutes difficultés',
                'required' => false,
            ])
            ->add('tempsMax', IntegerType::class, [
                'label' => 'Temps max (minutes)',
                'required' => false,
                'attr' => ['placeholder' => 'ex: 30']
            ])
            ->add('tri', ChoiceType::class, [
                'choices' => [
                    'Plus récentes' => 'date_desc',
                    'Plus anciennes' => 'date_asc',
                    'Mieux notées' => 'notes_desc',
                ],
                'placeholder' => 'Trier par',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false, // Pour requêtes GET simples
            'method' => 'GET',
        ]);
    }
}