<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Controller;

use App\Domain\Shared\Exception\AlreadyExistsDomainException;
use App\Domain\Shared\Exception\DomainException;
use App\Domain\Shared\Exception\ValidationErrorDomainException;
use App\Infrastructure\Web\Form\Helper\TranslationFormHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Exception\OutOfBoundsException;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class BaseController extends AbstractController
{
    public function __construct(
        protected readonly MessageBusInterface $bus,
        protected readonly TranslatorInterface $translator,
        protected readonly TranslationFormHelper $formHelper,
    ) {
    }

    protected function handleHandlerException(HandlerFailedException $e, FormInterface $form): void
    {
        $originalException = $e->getPrevious();

        if ($originalException instanceof ValidationErrorDomainException) {
            $errors = $originalException->getErrors();

            /**
             * @var string                                            $field
             * @var array{key: string, params?: array<string>}|string $error
             */
            foreach ($errors as $field => $error) {
                if (\is_array($error)) {
                    $message = $this->translator->trans($error['key'], $error['params'] ?? [], domain: 'validations');
                } else {
                    $message = $this->translator->trans($error, domain: 'validations');
                }

                try {
                    $formField = $form->get($field);

                    if ($formField->has('first') && $formField->has('second')) {
                        $formField->get('first')->addError(new FormError($message));
                    } else {
                        $formField->addError(new FormError($message));
                    }
                } catch (OutOfBoundsException) {
                    $this->addFlash('danger', $message);
                }
            }
        } elseif ($originalException instanceof AlreadyExistsDomainException) {
            try {
                $formField = $form->get($originalException->getField());

                if ($formField->has('first') && $formField->has('second')) {
                    $formField->get('first')->addError(new FormError($this->translator->trans('message.duplicate_value')));
                } else {
                    $formField->addError(new FormError($this->translator->trans('message.duplicate_value')));
                }
            } catch (OutOfBoundsException) {
                $form->addError(
                    new FormError(
                        $this->translator->trans(
                            'message.duplicate_value_field',
                            ['%field%' => $this->translator->trans('field.' . $originalException->getField())]
                        )
                    )
                );
            }
        } elseif ($originalException instanceof DomainException) {
            $form->addError(new FormError($e->getMessage()));
        } else {
            $this->addFlash('danger', $e->getMessage());
        }
    }
}
