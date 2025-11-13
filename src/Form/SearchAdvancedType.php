<?php

namespace App\Form;

use App\Entity\Categorie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchAdvancedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('query', SearchType::class, [
                'required' => false,
                'label' => 'Recherche',
                'attr' => ['placeholder' => 'Titre, ingrédient...']
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'required' => false,
                'placeholder' => 'Toutes catégories',
                'label' => 'Catégorie'
            ])
            ->add('difficulte', ChoiceType::class, [
                'choices' => [
                    'Facile' => 1,
                    'Moyen' => 2,
                    'Difficile' => 3
                ],
                'required' => false,
                'placeholder' => 'Toutes difficultés',
                'label' => 'Difficulté'
            ])
            ->add('tempsMax', ChoiceType::class, [
                'choices' => [
                    '15 min' => 15,
                    '30 min' => 30,
                    '45 min' => 45,
                    '60 min' => 60,
                    '90 min' => 90,
                    '120 min' => 120
                ],
                'required' => false,
                'placeholder' => 'Temps max',
                'label' => 'Temps max'
            ])
            ->add('tri', ChoiceType::class, [
                'choices' => [
                    'Plus récentes' => 'date_desc',
                    'Plus anciennes' => 'date_asc',
                    'Meilleures notes' => 'note_desc',
                    'Plus vues' => 'vue_desc'
                ],
                'label' => 'Trier par'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false
        ]);
    }
}