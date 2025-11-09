<?php

declare(strict_types=1);

namespace App\Application\Command\Product\CreateProduct;

use App\Domain\Product\ValueObject\Type;
use App\Domain\Shared\Entity\Country;
use App\Domain\Translation\Entity\TranslationEntity;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class CreateProductCommand
{
    /**
     * @param Collection<int, TranslationEntity> $translations
     */
    public function __construct(
        private Type $type,
        private Country $country,
        private ?UploadedFile $imageFile,
        private Collection $translations,
    ) {
    }

    public static function createFromForm(FormInterface $form): self
    {
        $data = $form->getData();

        return new self($data['type'], $data['country'], $data['imageFile'], $data['translations']);
    }

    /**
     * @return Collection<int, TranslationEntity>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function getImageFile(): ?UploadedFile
    {
        return $this->imageFile;
    }

    public function getCountry(): Country
    {
        return $this->country;
    }

    public function getType(): Type
    {
        return $this->type;
    }
}
