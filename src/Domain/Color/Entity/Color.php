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
    private ?int $id;

    #[ORM\Column(type: 'integer', length: 4, unique: true, nullable: false)]
    #[Assert\NotNull(message: 'not_null')]
    #[Assert\Range(
        notInRangeMessage: 'range',
        min: 1000,
        max: 9999
    )]
    private int $code;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $in_stock = false;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $enabled = true;

    protected function __construct(?int $id = null)
    {
        $this->id = $id;
        $this->initTranslations();
    }

    public static function create(int $code): self
    {
        self::validateCode($code);
        $product = new self();
        $product->code = $code;
        $product->in_stock = false;
        $product->enabled = true;

        return $product;
    }

    public static function createEmpty(): self
    {
        return new self();
    }

    public static function createFromDatabase(
        int $id,
        int $code,
        bool $in_stock,
        bool $enabled = true,
    ): self {
        $material = new self($id);
        $material->code = $code;
        $material->in_stock = $in_stock;
        $material->enabled = $enabled;

        return $material;
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

    protected function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getCode(): int
    {
        if (!isset($this->code)) {
            throw new \LogicException('Color code is not initialized');
        }

        return $this->code;
    }

    public function setCode(int $code): void
    {
        self::validateCode($code);
        $this->code = $code;
    }

    public function getDescription(string $locale = 'en'): ?string
    {
        return $this->getTranslationFromMemory('description', $locale);
    }

    public function setDescription(?string $value, string $locale = 'en'): void
    {
        if ($value !== null) {
            self::validateDescription($value);
        }
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
        return $this->in_stock;
    }

    public function setInStock(bool $inStock): void
    {
        $this->in_stock = $inStock;
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
