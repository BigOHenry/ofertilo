<?php

declare(strict_types=1);

namespace App\Application\Wood\Command\CreateWood;

use App\Domain\Translation\DTO\TranslationDto;
use App\Domain\Translation\Entity\TranslationEntity;
use Symfony\Component\Form\FormInterface;

final readonly class CreateWoodCommand
{
    /**
     * @param array<int, TranslationDto> $translations
     */
    public function __construct(
        private string $name,
        private ?string $latinName,
        private ?int $dryDensity,
        private ?int $hardness,
        private array $translations,
    ) {
    }

    public static function createFromForm(FormInterface $form): CreateWoodCommand
    {
        $data = $form->getData();

        $translations = [];
        /** @var TranslationEntity $translation */
        foreach ($data['translations'] as $translation) {
            $translations[] = TranslationDto::createTranslationDtoFromEntity($translation);
        }

        return new self($data['name'], $data['latinName'], $data['dryDensity'], $data['hardness'], $translations);
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

    /**
     * @return array<int, TranslationDto>
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }
}
