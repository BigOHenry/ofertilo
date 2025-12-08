<?php

declare(strict_types=1);

namespace App\Domain\Translation\TranslationDto;


use App\Domain\Translation\Entity\TranslationEntity;

class TranslationDto implements \Serializable
{
    protected function __construct(
        private ?int $id = null,
        private ?int $objectId = null,
        private string $objectClass,
        private string $locale,
        private string $field,
        private ?string $value = null
    ) {
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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getObjectClass(): string
    {
        return $this->objectClass;
    }

    public function getObjectId(): int
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

    public function unserialize($data): void
    {
        /** @var array $array */
        $array = unserialize($data, ['allowed_classes' => false]);
        $this->__unserialize($array);
    }

    public function __serialize(): array
    {
        return [
            'id'          => $this->id,
            'objectId'    => $this->objectId,
            'objectClass' => $this->objectClass,
            'locale'      => $this->locale,
            'field'       => $this->field,
            'value'       => $this->value,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data['id'] ?? null;
        $this->objectId = $data['objectId'];
        $this->objectClass = $data['objectClass'];
        $this->locale = $data['locale'];
        $this->field = $data['field'];
        $this->value = $data['value'] ?? null;
    }
}
