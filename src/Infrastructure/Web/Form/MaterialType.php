<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Form;

use App\Domain\Material\ValueObject\Type;
use App\Domain\Wood\Entity\Wood;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class MaterialType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
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
                    array_map(fn ($v) => $this->translator->trans($v->label(), domain: 'enum'), Type::cases()),
                    Type::cases()
                ),
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
