<?php

declare(strict_types=1);

namespace App\Application\Material\Command;

use App\Domain\Material\ValueObject\Type;
use App\Domain\Translation\Entity\TranslationEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

class CreateMaterialCommand
{
    #[Assert\NotNull(message: 'not_null')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'min_length',
        maxMessage: 'max_length'
    )]
    private ?string $name = null;

    #[Assert\NotNull(message: 'not_null')]
    private ?Type $type = null;

    #[Assert\Length(max: 300, maxMessage: 'length_max')]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z\s]+$/',
        message: 'invalid_characters'
    )]
    private ?string $latinName = null;

    #[Assert\Range(
        notInRangeMessage: 'range³',
        min: 10,
        max: 2000
    )]
    private ?int $dryDensity = null;

    #[Assert\Range(
        notInRangeMessage: 'range',
        min: 1,
        max: 9999
    )]
    private ?int $hardness = null;

    private bool $enabled = true;

    /**
     * @var Collection<int, TranslationEntity>
     */
    private Collection $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(?Type $type): void
    {
        $this->type = $type;
    }

    public function getLatinName(): ?string
    {
        return $this->latinName;
    }

    public function setLatinName(?string $latinName): void
    {
        $this->latinName = $latinName;
    }

    public function getDryDensity(): ?int
    {
        return $this->dryDensity;
    }

    public function setDryDensity(?int $dryDensity): void
    {
        $this->dryDensity = $dryDensity;
    }

    public function getHardness(): ?int
    {
        return $this->hardness;
    }

    public function setHardness(?int $hardness): void
    {
        $this->hardness = $hardness;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @return Collection<int, TranslationEntity>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    /**
     * @param Collection<int, TranslationEntity> $translations
     */
    public function setTranslations(Collection $translations): void
    {
        $this->translations = $translations;
    }
}
