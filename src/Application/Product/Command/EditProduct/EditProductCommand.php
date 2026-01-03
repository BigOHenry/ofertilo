<?php

declare(strict_types=1);

namespace App\Application\Product\Command\EditProduct;

use App\Domain\Translation\DTO\TranslationDto;
use App\Domain\Translation\Entity\TranslationEntity;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class EditProductCommand
{
    /**
     * @param array<int, TranslationDto> $translations
     */
    public function __construct(
        private int $id,
        private ?int $countryId,
        private ?UploadedFile $imageFile,
        private bool $enabled,
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

        return new self((int) $data['id'], $data['country']?->getId(), $data['imageFile'], $data['enabled'], $translations);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCountryId(): ?int
    {
        return $this->countryId;
    }

    public function getImageFile(): ?UploadedFile
    {
        return $this->imageFile;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return array<int, TranslationDto> $translations
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }
}
