<?php

namespace App\Form;

use App\Entity\Depense;
use App\Entity\CompteDepense;
use App\Entity\CategorieDepense;
use Symfony\Component\Form\AbstractType;
use App\Repository\CompteDepenseRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

class DepenseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('montant', MoneyType::class, [
                'label' => 'Montant'
            ])
            ->add('date', DateType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
                'html5' => 'false',
            ])
            ->add('compteDepense', EntityType::class, [
                'class' => CompteDepense::class,
                'query_builder' => function (CompteDepenseRepository $cdr) {
                    return $cdr->queryFindByCurrentUtilisateur();
                },
                'choice_label' => 'nom',
                'label' => 'Compte'
            ])
            ->add('categorie', EntityType::class, [
                'class' => CategorieDepense::class,
                'choice_label' => 'libelle',
                'label' => 'Catégorie',
                'choice_translation_domain' => true
            ])
            ->add('description', TextType::class, [
                'label' => 'Description',
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Depense::class,
        ]);
    }
}
