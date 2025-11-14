<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom complet',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Votre nom et prenom'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer votre nom'
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caracteres',
                        'maxMessage' => 'Le nom ne peut pas depasser {{ limit }} caracteres'
                    ])
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'votre.email@exemple.com'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer votre email'
                    ]),
                    new Email([
                        'message' => 'Veuillez entrer un email valide'
                    ])
                ]
            ])
            ->add('sujet', TextType::class, [
                'label' => 'Sujet',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Objet de votre message'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un sujet'
                    ]),
                    new Length([
                        'min' => 5,
                        'max' => 150,
                        'minMessage' => 'Le sujet doit contenir au moins {{ limit }} caracteres',
                        'maxMessage' => 'Le sujet ne peut pas depasser {{ limit }} caracteres'
                    ])
                ]
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Votre message',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 8,
                    'placeholder' => 'Ecrivez votre message ici...'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un message'
                    ]),
                    new Length([
                        'min' => 20,
                        'max' => 2000,
                        'minMessage' => 'Le message doit contenir au moins {{ limit }} caracteres',
                        'maxMessage' => 'Le message ne peut pas depasser {{ limit }} caracteres'
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Pas d'entité liée, juste un formulaire de contact
        ]);
    }
}