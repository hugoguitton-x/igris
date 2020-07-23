<?php

namespace App\Form;

use App\Entity\Serie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SerieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Name'
            ])
            ->add('image', FileType::class, [
                'label' => 'Picture',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image (max 1mb)',
                    ])
                ],
            ])
            ->add('synopsis', TextareaType::class, [
                'label' => 'Synopsis'
            ])
            ->add('lien', UrlType::class, [
                'label' => 'Link'
            ])
            ->add('nombreEpisodes', NumberType::class, [
                'label' => 'Number of episodes'
            ])
            ->add('dureeEpisode',  NumberType::class, [
                'label' => 'Duration of episodes'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Serie::class,
        ]);
    }
}
