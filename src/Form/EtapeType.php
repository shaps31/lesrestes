<?php

namespace App\Form;

use App\Entity\Etape;
use App\Entity\Recette;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Type\TextareaType;
use Symfony\Component\Form\Extension\Type\HiddenType;


class EtapeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('description', TextareaType::class, [
            'label' => 'Description de l\'étape',
            'attr' => ['rows' => 3, 'placeholder' => 'Ex: Coupez les légumes en dés, faites revenir à feu doux, etc.']
        ])
        
        ->add('ordre', HiddenType::class, [ 
             'label' => false, 
        ])
       
    ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Etape::class,
        ]);
    }
}
