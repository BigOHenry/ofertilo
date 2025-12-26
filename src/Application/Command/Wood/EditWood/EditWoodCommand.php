<?php

declare(strict_types=1);

namespace App\Application\Command\Wood\EditWood;

use App\Domain\Translation\Entity\TranslationEntity;
use App\Domain\Translation\TranslationDto\TranslationDto;
use Symfony\Component\Form\FormInterface;

final readonly class EditWoodCommand
{
    /**
     * @param array<int, TranslationDto> $translations
     */
    public function __construct(
        private int $id,
        private string $name,
        private ?string $latinName,
        private ?int $dryDensity,
        private ?int $hardness,
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

        return new self(
            (int) $data['id'],
            $data['name'],
            $data['latinName'],
            $data['dryDensity'],
            $data['hardness'],
            $data['enabled'],
            $translations
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLatinName(): ?string
    {
        return $this->latinName;
    }

    public function getDryDensity(): ?int
    {
        return $this->dryDensity;
    }

    public function getHardness(): ?int
    {
        return $this->hardness;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return array<int, TranslationDto>
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }
}
