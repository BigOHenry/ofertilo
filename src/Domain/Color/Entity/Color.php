<?php

declare(strict_types=1);

namespace App\Domain\Color\Entity;

use App\Domain\Color\Exception\InvalidColorException;
use App\Domain\Translation\Interface\TranslatableInterface;
use App\Domain\Translation\Trait\TranslatableTrait;
use App\Infrastructure\Persistence\Doctrine\DoctrineColorRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
    #[Assert\NotNull(message: 'not_null')]
    #[Assert\Range(
        notInRangeMessage: 'range',
        min: 1000,
        max: 9999
    )]
    private int $code;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $inStock = false;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $enabled = true;

    protected function __construct()
    {
        $this->initTranslations();
    }

    public static function create(int $code, bool $inStock = false, bool $enabled = true): self
    {
        self::validateCode($code);
        $color = new self();
        $color->code = $code;
        $color->inStock = $inStock;
        $color->enabled = $enabled;

        return $color;
    }

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

    public function getCode(): int
    {
        if (!isset($this->code)) {
            throw new \LogicException('Color code is not initialized');
        }

        return $this->code;
    }

    public function setCode(int $code): self
    {
        self::validateCode($code);
        $this->code = $code;

        return $this;
    }

    public function getDescription(string $locale = 'en'): ?string
    {
        return $this->getTranslationFromMemory('description', $locale);
    }

    public function setDescription(?string $value, string $locale = 'en'): self
    {
        if ($value !== null) {
            self::validateDescription($value);
        }
        $this->addOrUpdateTranslation('description', $value, $locale);

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function isInStock(): bool
    {
        return $this->inStock;
    }

    public function setInStock(bool $inStock): self
    {
        $this->inStock = $inStock;

        return $this;
    }

    protected function setId(int $id): void
    {
        $this->id = $id;
    }

    private static function validateDescription(string $description): void
    {
        $trimmed = mb_trim($description);
        if (mb_strlen($trimmed) > 100) {
            throw InvalidColorException::descriptionTooLong(100);
        }
    }

    private static function validateCode(int $code): void
    {
        if ($code < 1000) {
            throw InvalidColorException::codeTooLow(1000);
        }

        if ($code > 9999) {
            throw InvalidColorException::codeTooHigh(9999);
        }
    }
}
