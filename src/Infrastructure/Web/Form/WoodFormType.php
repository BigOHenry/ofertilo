<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Form;

use App\Domain\Wood\Entity\Wood;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WoodFormType extends AbstractType
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
            ->add('translations', TranslationsFormType::class, [
                'mapped' => true,
                'label' => false,
                'entity_class' => Wood::class,
            ])
            ->add('latinName', TextType::class, [
                'label' => 'field.latinName',
                'required' => false,
            ])
            ->add('dryDensity', IntegerType::class, [
                'label' => 'field.dryDensity',
                'required' => true,
            ])
            ->add('hardness', IntegerType::class, [
                'label' => 'field.hardness',
                'required' => true,
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
