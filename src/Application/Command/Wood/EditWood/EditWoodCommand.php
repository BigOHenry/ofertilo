<?php

declare(strict_types=1);

namespace App\Application\Command\Wood\EditWood;

use App\Domain\Translation\Entity\TranslationEntity;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\FormInterface;

final readonly class EditWoodCommand
{
    /**
     * @param int                                $id
     * @param string                             $name
     * @param string|null                        $latinName
     * @param int|null                           $dryDensity
     * @param int|null                           $hardness
     * @param bool                               $enabled
     * @param Collection<int, TranslationEntity> $translations
     */
    public function __construct(
        private int $id,
        private string $name,
        private ?string $latinName,
        private ?int $dryDensity,
        private ?int $hardness,
        private bool $enabled,
        private Collection $translations,
    ) {
    }

    public static function createFromForm(FormInterface $form): self
    {
        $data = $form->getData();

        return new self(
            (int) $data['id'],
            $data['name'],
            $data['latinName'],
            $data['dryDensity'],
            $data['hardness'],
            $data['enabled'],
            $data['translations']
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
     * @return Collection<int, TranslationEntity>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }
}
