<?php

declare(strict_types=1);

namespace App\Form;

use App\Domain\Product\Entity\Product;
use App\Domain\Product\ValueObject\Type;
use App\Domain\Shared\Entity\Country;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('translations', CollectionType::class, [
                'entry_type' => TranslationFormType::class,
                'mapped' => true,
                'by_reference' => false,
                'label' => false,
                'entry_options' => [
                    'label' => false,
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'field.type',
                'choices' => array_combine(
                    array_map(fn ($v) => $this->translator->trans($v->label(), domain: 'enum'), Type::cases()),
                    Type::cases()
                ),
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
                'placeholder' => 'form.choose_country',
                'required' => true,
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'field.image',
                'mapped' => false, // Není mapováno přímo na entitu
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp',
                            'image/svg+xml',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image file (JPEG, PNG, GIF, WebP, SVG)',
                    ]),
                ],
                'help' => 'Maximum file size: 2MB. Allowed formats: JPEG, PNG, GIF, WebP, SVG',
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'field.enabled',
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
            'data_class' => Product::class,
            'translation_domain' => 'messages',
        ]);
    }
}
