<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'translation')]
class TranslationEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(length: 100, nullable: false)]
    private string $object_class;

    #[ORM\Column(nullable: false)]
    private int $object_id;

    #[ORM\Column(length: 2, nullable: false)]
    private string $locale;

    #[ORM\Column(length: 200, nullable: false)]
    private string $field;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $value = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getObjectClass(): string
    {
        return $this->object_class;
    }

    public function setObjectClass(string $objectClass): void
    {
        $this->object_class = $objectClass;
    }

    public function getObjectId(): int
    {
        return $this->object_id;
    }

    public function setObjectId(int $objectId): void
    {
        $this->object_id = $objectId;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function setField(string $field): void
    {
        $this->field = $field;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): void
    {
        $this->value = $value;
    }
}
