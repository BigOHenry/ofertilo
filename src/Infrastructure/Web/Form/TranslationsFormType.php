<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Form;

use App\Domain\Translation\Entity\TranslationEntity;
use App\Infrastructure\Service\LocaleService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

final class TranslationsFormType extends AbstractType implements DataMapperInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly LocaleService $localeService,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $entityClass = $options['entity_class'];
        $translatableFields = $entityClass::getTranslatableFields();
        $supportedLocales = $this->localeService->getSupportedLocales();
        $translator = $this->translator;

        // Create an array of containers (name, description, short description)
        foreach ($translatableFields as $field) {
            $builder->add($field, FormType::class, [
                'label' => false,
                'compound' => true,
            ]);
        }

        // Add locale fields only in PRE_SET_DATA, when the data is available
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($translatableFields, $supportedLocales, $translator): void {
                $form = $event->getForm();
                /** @var TranslationEntity[]|null $translations */
                $translations = $event->getData();

                if ($translations === null || empty($translations)) {
                    return;
                }

                // Create a map field => locale => value for quick search
                $dataMap = [];
                foreach ($translations as $translation) {
                    $dataMap[$translation->getField()][$translation->getLocale()] = $translation->getValue();
                }

                // Add locale fields to each form field
                foreach ($translatableFields as $field) {
                    if (!$form->has($field)) {
                        continue;
                    }

                    $fieldForm = $form->get($field);

                    foreach ($supportedLocales as $locale) {
                        $fieldLabel = \sprintf(
                            '%s (%s)',
                            $translator->trans('field.' . $field, domain: 'messages'),
                            mb_strtoupper($locale)
                        );

                        $value = $dataMap[$field][$locale] ?? '';

                        $fieldForm->add($locale, TextType::class, [
                            'label' => $fieldLabel,
                            'required' => false,
                            'data' => $value,
                        ]);
                    }
                }
            }
        );

        $builder->setDataMapper($this);
    }

    /**
     * @param TranslationEntity[]|null            $viewData
     * @param \Traversable<string, FormInterface> $forms
     */
    public function mapDataToForms($viewData, \Traversable $forms): void
    {
        // Data is already set in PRE_SET_DATA
        // This method is not called because we set the data directly via 'data' => $value
    }

    /**
     * Maps data from the form back to TranslationEntity[].
     *
     * @param \Traversable<string, FormInterface> $forms
     * @param TranslationEntity[]|null            $viewData
     */
    public function mapFormsToData(\Traversable $forms, $viewData): void
    {
        if ($viewData === null || empty($viewData)) {
            return;
        }

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        // Iteruj přes původní TranslationEntity objekty
        foreach ($viewData as $translation) {
            $field = $translation->getField();
            $locale = $translation->getLocale();

            if (!isset($forms[$field])) {
                continue;
            }

            $fieldForm = $forms[$field];
            if ($fieldForm->has($locale)) {
                $value = $fieldForm->get($locale)->getData();
                $translation->setValue($value);
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'entity_class' => null,
        ]);

        $resolver->setRequired('entity_class');
    }
}
