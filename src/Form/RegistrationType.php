<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'attr' => [
                    'placeholder' => 'Username',
                    'autofocus' => true
                ] 
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'placeholder' => 'Email'
                ] 
            ])
            ->add('firstname', TextType::class, [
                'attr' => [
                    'placeholder' => 'First Name'
                ] 
            ])
            ->add('lastname', TextType::class, [
                'attr' => [
                    'placeholder' => 'Last Name'
                ] 
            ])
            ->add('password', PasswordType::class, [
                'attr' => [
                    'placeholder' => 'Password'
                ] 
            ])
            ->add('password_confirm', PasswordType::class, [
                'attr' => [
                    'placeholder' => 'Confirm password'
                ] 
            ])
            ->add('avatar', FileType::class, [
                'label' => 'Picture',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Image([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image (max 1mb)',
                        'allowLandscape' => false,
                        'allowPortrait' => false
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
