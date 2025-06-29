<?php

declare(strict_types=1);

namespace App\Form;

use App\Domain\Material\ValueObject\Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class MaterialType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'field.name',
                'property_path' => 'name',
            ])
            ->add('translations', CollectionType::class, [
                'entry_type' => TranslationFormType::class,
                'mapped' => true,
                'by_reference' => false,
                'label' => false,
                'property_path' => 'translations',
                'entry_options' => [
                    'label' => false,
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'field.type',
                'property_path' => 'type',
                'choices' => array_combine(
                    array_map(fn ($v) => $this->translator->trans($v->label(), domain: 'enum'), Type::cases()),
                    Type::cases()
                ),
            ])
            ->add('latin_name', TextType::class, [
                'label' => 'field.latin_name',
                'property_path' => 'latin_name',
                'required' => false,
            ])
            ->add('dry_density', IntegerType::class, [
                'label' => 'field.dry_density',
                'property_path' => 'dry_density',
                'required' => false,
            ])
            ->add('hardness', IntegerType::class, [
                'label' => 'field.hardness',
                'property_path' => 'hardness',
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
            'data_class' => null,
            'translation_domain' => 'messages',
        ]);
    }
}
