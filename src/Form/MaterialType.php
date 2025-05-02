<?php

namespace App\Form;

use App\Domain\Material\Material;
use App\Domain\Material\Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class MaterialType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'field.name',
            ])
            ->add('description', TextType::class, [
                'label' => 'field.description',
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'field.type',
                'choices' => array_combine(
                    array_map(fn($v) => $this->translator->trans($v->label(), domain: 'enum'), Type::cases()),
                    Type::cases()
                ),
            ])
            ->add('save', SubmitType::class, [
                'label' => 'button.save'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Material::class,
            'translation_domain' => 'messages',
        ]);
    }
}
