<?php

declare(strict_types=1);

namespace App\Infrastructure\Form;

use App\Domain\Color\Entity\Color;
use App\Domain\Color\Repository\ColorRepositoryInterface;
use App\Domain\Product\Entity\Product;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductColorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $product = $builder->getData()['product'] ?? null;
        $color = $builder->getData()['color'] ?? null;

        $builder
            ->add('id', HiddenType::class, [
                'label' => 'field.id',
            ])
            ->add('color', EntityType::class, [
                'label' => 'field.color',
                'class' => Color::class,
                'choice_label' => 'code',
                'query_builder' => function (ColorRepositoryInterface $repository) use ($product, $color): QueryBuilder {
                    $qb = $repository->createQueryBuilder('c');

                    if ($product && $product->getId()) {
                        $assignedColorIds = $product->getProductColors()->map(
                            fn($pc) => $pc->getColor()->getId()
                        )->toArray();

                        if ($color !== null) {
                            $assignedColorIds = array_filter(
                                $assignedColorIds,
                                static fn($colorId) => $colorId !== $color->getId()
                            );
                        }

                        if (!empty($assignedColorIds)) {
                            $qb->where($qb->expr()->notIn('c.id', ':assignedIds'))
                               ->setParameter('assignedIds', $assignedColorIds);
                        }
                    }

                    return $qb->orderBy('c.code', 'ASC');
                },
                'placeholder' => 'form.choose_color',
                'required' => true,
            ])
            ->add('description', TextType::class, [
                'label' => 'field.description',
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
