<?php

declare(strict_types=1);

namespace App\Form;

use App\Domain\Color\Entity\Color;
use App\Domain\Product\Entity\ProductColor;
use Doctrine\ORM\EntityRepository;
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
        $builder
            ->add('color', EntityType::class, [
                'label' => 'field.color',
                'class' => Color::class,
                'choice_label' => 'code',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                              ->where('c.enabled = :enabled')
                              ->setParameter('enabled', true)
                              ->orderBy('c.code', 'ASC');
                },
                'placeholder' => 'form.choose_color',
                'required' => true,
            ])
            ->add('description', TextType::class, [
                'label' => 'field.description',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'button.save',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductColor::class,
            'translation_domain' => 'messages',
        ]);
    }
}
