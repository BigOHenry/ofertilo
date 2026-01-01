<?php

declare(strict_types=1);

namespace App\Domain\Color\Entity;

use App\Domain\Translation\Interface\TranslatableInterface;
use App\Domain\Translation\Trait\TranslatableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'color')]
class Color implements TranslatableInterface
{
    use TranslatableTrait;

    public const string TRANSLATION_FIELD_DESCRIPTION = 'description';

    private const array TRANSLATION_FIELDS = [
        self::TRANSLATION_FIELD_DESCRIPTION,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'integer', length: 4, unique: true, nullable: false)]
    private int $code;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $inStock = false;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $enabled = true;

    protected function __construct()
    {
        $this->initializeTranslations();
    }

    public static function create(int $code, bool $inStock = false, bool $enabled = true): self
    {
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
        return self::TRANSLATION_FIELDS;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function setCode(int $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getDescription(string $locale = 'en'): ?string
    {
        return $this->getTranslationValue(self::TRANSLATION_FIELD_DESCRIPTION, $locale);
    }

    public function setDescription(?string $value, string $locale = 'en'): self
    {
        $this->addOrUpdateTranslation(self::TRANSLATION_FIELD_DESCRIPTION, $value, $locale);

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
}
