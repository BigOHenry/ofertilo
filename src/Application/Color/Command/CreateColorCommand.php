<?php

namespace App\Application\Color\Command;

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
     * Translations array structure:
     * [
     *   'en' => ['description' => 'English description'],
     *   'cs' => ['description' => 'Czech description']
     * ]
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

    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function setTranslations(Collection $translations): void
    {
        $this->translations = $translations;
    }
}
