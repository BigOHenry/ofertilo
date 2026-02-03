<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Form;

use App\Domain\Color\Entity\Color;
use App\Domain\Product\Entity\Product;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductColorFormType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Product|null $product */
        $product = $builder->getData()['product'] ?? null;
        /** @var Color|null $color */
        $color = $builder->getData()['color'] ?? null;

        $builder
            ->add('id', HiddenType::class, [
                'label' => 'field.id',
            ])
            ->add('color', EntityType::class, [
                'label' => 'field.color',
                'class' => Color::class,
                'choice_label' => function (Color $color): string {
                    return \sprintf('%s - %s', $color->getCode(), $color->getDescription($this->translator->getLocale()));
                },
                'query_builder' => static function (EntityRepository $repository) use ($product, $color): QueryBuilder {
                    $qb = $repository->createQueryBuilder('c');

                    if ($product && $product->getId()) {
                        $assignedColorIds = $product->getProductColors()->map(
                            static fn ($pc) => $pc->getColor()->getId()
                        )->toArray();

                        if ($color !== null) {
                            $assignedColorIds = array_filter(
                                $assignedColorIds,
                                static fn ($colorId) => $colorId !== $color->getId()
                            );
                        }

                        if (!empty($assignedColorIds)) {
                            $qb->where($qb->expr()->notIn('c.id', ':assignedIds'))
                               ->setParameter('assignedIds', $assignedColorIds)
                            ;
                        }
                    }

                    return $qb->orderBy('c.code', 'ASC');
                },
                'placeholder' => 'form.choose_color',
                'required' => true,
                'autocomplete' => true,
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
