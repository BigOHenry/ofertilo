<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ColorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', HiddenType::class)
            ->add('code', NumberType::class, [
                'label' => 'field.code',
                'scale' => 0,
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
            ->add('inStock', CheckboxType::class, [
                'label' => 'field.inStock',
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
