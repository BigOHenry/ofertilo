<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;

class FirstRunSetupFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'field.email',
                'translation_domain' => 'messages',
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'field.password',
                    'translation_domain' => 'messages',
                ],
                'second_options' => [
                    'label' => 'field.confirm_password',
                    'translation_domain' => 'messages',
                ],
                'invalid_message' => 'password_mismatch',
            ])
        ;
    }
}
