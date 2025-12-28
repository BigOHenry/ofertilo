<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Form;

use App\Domain\Material\Entity\Material;
use App\Domain\Material\ValueObject\MeasurementType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MaterialPriceCalculationFormType extends AbstractType
{
    public function __construct()
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Material $material */
        $material = $options['material'];

        if ($material->getMeasurementType() === MeasurementType::VOLUME) {
            $builder
                ->add('thickness', IntegerType::class, [
                    'label' => 'field.thickness',
                ])
            ;
        }

        $builder
            ->add('length', IntegerType::class, [
                'label' => 'field.length',
            ])
            ->add('width', IntegerType::class, [
                'label' => 'field.width',
            ])
            ->add('price', TextType::class, [
                'label' => 'field.price',
            ])
            ->add('calculate', SubmitType::class, [
                'label' => 'button.calculate',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'translation_domain' => 'messages',
            'material' => null,
        ]);

        $resolver->setAllowedTypes('material', ['null', Material::class]);
    }
}
