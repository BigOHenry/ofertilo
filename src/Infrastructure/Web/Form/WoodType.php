<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WoodType extends AbstractType
{
    public function __construct()
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'field.name',
            ])
            ->add('translations', CollectionType::class, [
                'entry_type' => TranslationFormType::class,
                'mapped' => true,
                'by_reference' => false,
                'label' => false,
                'entry_options' => [
                    'label' => false,
                ],
            ])
            ->add('latinName', TextType::class, [
                'label' => 'field.latinName',
                'required' => false,
            ])
            ->add('dryDensity', IntegerType::class, [
                'label' => 'field.dryDensity',
                'required' => false,
            ])
            ->add('hardness', IntegerType::class, [
                'label' => 'field.hardness',
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'button.save',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'translation_domain' => 'messages',
        ]);
    }
}
