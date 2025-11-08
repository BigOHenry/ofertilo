<?php

declare(strict_types=1);

namespace App\Application\Command\Wood\CreateWood;

use App\Domain\Translation\Entity\TranslationEntity;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\FormInterface;

final readonly class CreateWoodCommand
{
    /**
     * @param string                             $name
     * @param string|null                        $latinName
     * @param int|null                           $dryDensity
     * @param int|null                           $hardness
     * @param Collection<int, TranslationEntity> $translations
     */
    public function __construct(
        private string $name,
        private ?string $latinName,
        private ?int $dryDensity,
        private ?int $hardness,
        private Collection $translations,
    ) {
    }

    public static function createFromForm(FormInterface $form): CreateWoodCommand
    {
        $data = $form->getData();

        return new self($data['name'], $data['latinName'], $data['dryDensity'], $data['hardness'], $data['translations']);
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
     * @return Collection<int, TranslationEntity>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }
}
