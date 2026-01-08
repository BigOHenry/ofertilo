<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductComponentFormType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantity', IntegerType::class, [
                'label' => 'field.quantity',
            ])
            ->add('length', IntegerType::class, [
                'label' => 'field.length',
            ])
            ->add('width', IntegerType::class, [
                'label' => 'field.width',
            ])
            ->add('thickness', IntegerType::class, [
                'label' => 'field.thickness',
            ])
            ->add('shapeDescription', TextareaType::class, [
                'label' => 'field.shapeDescription',
                'required' => false,
            ])
            ->add('blueprintFile', FileType::class, [
                'label' => 'field.blueprintFile',
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
