<?php

declare(strict_types=1);

namespace App\Domain\Translation\DTO;

use App\Domain\Translation\Entity\TranslationEntity;

class TranslationDto implements \Serializable
{
    protected function __construct(
        private string $id,
        private ?string $objectId,
        private string $objectClass,
        private string $locale,
        private string $field,
        private ?string $value = null,
    ) {
    }

    /**
     * @return array{id: string, objectId: string|null, objectClass: string, locale: string, field: string, value: string|null}
     */
    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'objectId' => $this->objectId,
            'objectClass' => $this->objectClass,
            'locale' => $this->locale,
            'field' => $this->field,
            'value' => $this->value,
        ];
    }

    /**
     * @param array{id: string, objectId: string, objectClass: string, locale: string, field: string, value: string|null} $data
     */
    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->objectId = $data['objectId'];
        $this->objectClass = $data['objectClass'];
        $this->locale = $data['locale'];
        $this->field = $data['field'];
        $this->value = $data['value'] ?? null;
    }

    public static function createTranslationDtoFromEntity(TranslationEntity $translation): self
    {
        return new self(
            $translation->getId(),
            $translation->getObjectId(),
            $translation->getObjectClass(),
            $translation->getLocale(),
            $translation->getField(),
            $translation->getValue()
        );
    }

    public static function create(
        string $id,
        string $objectId,
        string $objectClass,
        string $locale,
        string $field,
        ?string $value = null,
    ): self {
        return new self(
            id: $id,
            objectId: $objectId,
            objectClass: $objectClass,
            locale: $locale,
            field: $field,
            value: $value
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getObjectClass(): string
    {
        return $this->objectClass;
    }

    public function getObjectId(): ?string
    {
        return $this->objectId;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function serialize(): string
    {
        return serialize($this->__serialize());
    }

    public function unserialize(string $data): void
    {
        /** @var array{id: string, objectId: string, objectClass: string, locale: string, field: string, value: string|null} $array */
        $array = unserialize($data, ['allowed_classes' => false]);
        $this->__unserialize($array);
    }
}
