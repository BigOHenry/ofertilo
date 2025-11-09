<?php

declare(strict_types=1);

namespace App\Application\Command\Product\EditProduct;

use App\Domain\Product\ValueObject\Type;
use App\Domain\Shared\Entity\Country;
use App\Domain\Translation\Entity\TranslationEntity;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class EditProductCommand
{
    /**
     * @param Collection<int, TranslationEntity> $translations
     */
    public function __construct(
        private int $id,
        private Type $type,
        private Country $country,
        private ?UploadedFile $imageFile,
        private bool $enabled,
        private Collection $translations
    ) {
    }

    public static function createFromForm(FormInterface $form): self
    {
        $data = $form->getData();

        return new self((int) $data['id'], $data['type'], $data['country'], $data['imageFile'], $data['enabled'], $data['translations']);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function getCountry(): Country
    {
        return $this->country;
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
     * @return Collection<int, TranslationEntity> $translations
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }
}
