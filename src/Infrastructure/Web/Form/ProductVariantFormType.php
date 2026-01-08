<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductVariantFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('height', IntegerType::class, [
                'label' => 'field.height',
            ])
            ->add('width', IntegerType::class, [
                'label' => 'field.width',
            ])
            ->add('thickness', IntegerType::class, [
                'label' => 'field.thickness',
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
