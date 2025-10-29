<?php

declare(strict_types=1);

namespace App\Infrastructure\Form;

use App\Domain\Color\Entity\Color;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductColorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $availableColors = $options['available_colors'];
        $builder
            ->add('color', EntityType::class, [
                'label' => 'field.color',
                'class' => Color::class,
                'choice_label' => 'code',
                'property_path' => 'color',
                'choices' => $availableColors,
                'placeholder' => 'form.choose_color',
                'required' => true,
            ])
            ->add('description', TextType::class, [
                'label' => 'field.description',
                'property_path' => 'description',
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
            'available_colors' => [],
            'translation_domain' => 'messages',
        ]);

        $resolver->setAllowedTypes('available_colors', 'array');
    }
}
