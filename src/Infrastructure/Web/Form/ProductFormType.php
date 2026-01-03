<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Form;

use App\Domain\Product\Entity\Product;
use App\Domain\Product\ValueObject\ProductType;
use App\Domain\Shared\Country\Entity\Country;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductFormType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $productId = $builder->getData()['id'] ?? null;
        $enabled = $productId === null ? true : ($builder->getData()['enabled'] ?? null);

        $builder
            ->add('translations', TranslationsFormType::class, [
                'mapped' => true,
                'label' => false,
                'entity_class' => Product::class,
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'field.type',
                'choices' => array_combine(
                    array_map(fn ($v) => $this->translator->trans($v->label(), domain: 'enum'), ProductType::cases()),
                    ProductType::cases()
                ),
            ])
            ->add('code', TextType::class, [
                'label' => 'field.code',
            ])
            ->add('country', EntityType::class, [
                'label' => 'field.country',
                'class' => Country::class,
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                              ->where('c.enabled = :enabled')
                              ->setParameter('enabled', true)
                              ->orderBy('c.name', 'ASC')
                    ;
                },
                'placeholder' => '',
                'required' => false,
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'field.image',
                'mapped' => true,
                'required' => false,
                'constraints' => [
                    new File(
                        maxSize: '2M',
                        mimeTypes: [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp',
                            'image/svg+xml',
                        ],
                        mimeTypesMessage: $this->translator->trans('message.invalid_file_type_with_allowed_formats', [
                            '%formats%' => 'JPEG, PNG, GIF, WebP, SVG',
                        ])
                    ),
                ],
                'help' => $this->translator->trans('message.file_help_message', [
                    '%size%' => '2MB',
                    '%formats%' => 'JPEG, PNG, GIF, WebP, SVG',
                ]),
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'field.enabled',
                'required' => false,
                'data' => $enabled,
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
