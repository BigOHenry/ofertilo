<?php

declare(strict_types=1);

namespace App\Application\Color\Command;

use App\Domain\Translation\Entity\TranslationEntity;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

class CreateColorCommand
{
    #[Assert\NotNull(message: 'not_null')]
    #[Assert\Range(
        notInRangeMessage: 'range',
        min: 1000,
        max: 9999
    )]
    private ?int $code = null;

    #[Assert\Length(
        max: 100,
        maxMessage: 'max_length'
    )]
    private bool $in_stock = false;

    private bool $enabled = true;

    /**
     * @var Collection<int, TranslationEntity>
     */
    private Collection $translations;

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function isInStock(): bool
    {
        return $this->in_stock;
    }

    public function setInStock(bool $in_stock): void
    {
        $this->in_stock = $in_stock;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode(?int $code): void
    {
        $this->code = $code;
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
