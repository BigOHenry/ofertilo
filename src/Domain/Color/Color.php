<?php

declare(strict_types=1);

namespace App\Domain\Color;

use App\Domain\Translation\TranslatableInterface;
use App\Domain\Translation\TranslatableTrait;
use App\Infrastructure\Persistence\Doctrine\DoctrineColorRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DoctrineColorRepository::class)]
#[ORM\Table(name: 'color')]
class Color implements TranslatableInterface
{
    use TranslatableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'integer', length: 4, unique: true, nullable: false)]
    private ?int $code = null;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $inStock = false;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $enabled = true;

    /**
     * @return string[]
     */
    public static function getTranslatableFields(): array
    {
        return ['description'];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode(?int $code): void
    {
        $this->code = $code;
    }

    public function getDescription(?string $locale = null): ?string
    {
        return $this->getTranslationFromMemory('description', $locale ?? 'en');
    }

    public function setDescription(string $value, string $locale = 'en'): void
    {
        $this->addOrUpdateTranslation('description', $value, $locale);
    }

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
        return $this->inStock;
    }

    public function setInStock(bool $inStock): void
    {
        $this->inStock = $inStock;
    }
}
