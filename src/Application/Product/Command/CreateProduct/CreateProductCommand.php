<?php

declare(strict_types=1);

namespace App\Application\Product\Command\CreateProduct;

use App\Domain\Product\ValueObject\ProductType;
use App\Domain\Translation\DTO\TranslationDto;
use App\Domain\Translation\Entity\TranslationEntity;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class CreateProductCommand
{
    /**
     * @param array<int, TranslationDto> $translations
     */
    public function __construct(
        public ProductType $type,
        public ?int $countryId,
        public string $code,
        public ?UploadedFile $imageFile, // TODO replace this object
        public array $translations,
    ) {
    }

    public static function createFromForm(FormInterface $form): self
    {
        $data = $form->getData();

        $translations = [];
        /** @var TranslationEntity $translation */
        foreach ($data['translations'] as $translation) {
            $translations[] = TranslationDto::createTranslationDtoFromEntity($translation);
        }

        return new self($data['type'], $data['country']?->getId(), $data['code'], $data['imageFile'], $translations);
    }
}
