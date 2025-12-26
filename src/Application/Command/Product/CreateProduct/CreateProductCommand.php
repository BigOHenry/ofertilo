<?php

declare(strict_types=1);

namespace App\Application\Command\Product\CreateProduct;

use App\Domain\Product\ValueObject\ProductType;
use App\Domain\Translation\Entity\TranslationEntity;
use App\Domain\Translation\TranslationDto\TranslationDto;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class CreateProductCommand
{
    /**
     * @param array<int, TranslationDto> $translations
     */
    public function __construct(
        private ProductType $type,
        private ?int $countryId,
        private ?UploadedFile $imageFile, // TODO replace this object
        private array $translations,
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

        return new self($data['type'], $data['country']?->getId(), $data['imageFile'], $translations);
    }

    /**
     * @return array<int, TranslationDto>
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }

    public function getImageFile(): ?UploadedFile
    {
        return $this->imageFile;
    }

    public function getCountryId(): ?int
    {
        return $this->countryId;
    }

    public function getType(): ProductType
    {
        return $this->type;
    }
}
