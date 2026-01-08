<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Form;

use App\Domain\Color\Entity\Color;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ColorFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $colorId = $builder->getData()['id'] ?? null;
        $inStock = $colorId === null ? true : ($builder->getData()['inStock'] ?? null);

        $builder
            ->add('id', HiddenType::class)
            ->add('code', NumberType::class, [
                'label' => 'field.code',
                'scale' => 0,
            ])
            ->add('translations', TranslationsFormType::class, [
                'mapped' => true,
                'label' => false,
                'entity_class' => Color::class,
            ])
            ->add('inStock', CheckboxType::class, [
                'label' => 'field.inStock',
                'required' => false,
                'data' => $inStock,
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
