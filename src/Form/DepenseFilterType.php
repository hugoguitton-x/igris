<?php

namespace App\Form;

use App\Data\DepenseSearchData;
use App\Entity\CategorieDepense;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class DepenseFilterType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      ->add('categories', EntityType::class, [
        'label' => false,
        'required' => false,
        'class' => CategorieDepense::class,
        'choice_label' => 'libelle',
        'multiple' => true,
        'attr' => ['class' => 'selectpicker', 'title' => 'Sélectionner catégorie']
      ])
      ->add('date', DateType::class, [
        'label' => false,
        'attr' => ['class' => 'form-date-filter'],
        'required' => true,
      ]);
  }

  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults([
      'data_class' => DepenseSearchData::class,
      'method' => 'GET',
      'csrf_protection' => false
    ]);
  }

  public function getBlockPrefix()
  {
    return '';
  }
}
