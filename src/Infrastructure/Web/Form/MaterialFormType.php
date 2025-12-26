<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Form;

use App\Domain\Material\ValueObject\MaterialType;
use App\Domain\Wood\Entity\Wood;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class MaterialFormType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $materialId = $builder->getData()['id'] ?? null;

        $builder
            ->add('wood', EntityType::class, [
                'label' => 'field.wood',
                'class' => Wood::class,
                'choice_label' => 'description',
                'placeholder' => 'form.choose_wood',
                'required' => true,
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'field.type',
                'choices' => array_combine(
                    array_map(fn ($v) => $this->translator->trans($v->label(), domain: 'enum'), MaterialType::cases()),
                    MaterialType::cases()
                ),
                'required' => true,
                'disabled' => $materialId !== null,
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
