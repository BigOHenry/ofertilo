<?php

declare(strict_types=1);

namespace App\Domain\Shared\Validator;

use App\Domain\Shared\File\Entity\File;
use App\Domain\Shared\File\ValueObject\FileType;
use App\Domain\Translation\DTO\TranslationDto;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Validator
{
    /**
     * @param TranslationDto[]               $translations
     * @param array<string, callable|string> $rules
     *
     * @return array<string, array{key: string, params?: array<string, int|null>}>
     */
    public static function validateTranslatableFields(array $translations, array $rules): array
    {
        $errors = [];

        foreach ($translations as $dto) {
            $field = $dto->getField();
            $locale = $dto->getLocale();
            $value = $dto->getValue();

            if (!isset($rules[$field])) {
                continue;
            }

            $rule = $rules[$field];

            if (\is_callable($rule)) {
                $fieldErrors = $rule($value, $locale, $field, $dto);
            } elseif (method_exists(static::class, $rule)) {
                /** @var callable $callback */
                $callback = [static::class, $rule];
                $fieldErrors = $callback($value, $locale, $field, $dto);
            } else {
                continue;
            }

            foreach ($fieldErrors as $k => $err) {
                // The key must be translations.field.locale
                $errors[\sprintf('translations.%s.%s', $field, $locale)] = $err;
            }
        }

        return $errors;
    }

    /**
     * @return array<string, array{key: string, params?: array<string, float|int|string|null>}>
     */
    public static function validateUploadedFile(string $field, ?UploadedFile $file, FileType $type): array
    {
        if ($file === null) {
            return [];
        }

        $mimeType = $file->getMimeType();
        $size = $file->getSize();
        $errors = [];
        $allowedMimeTypes = match ($type) {
            FileType::IMAGE => File::IMAGE_MIME_TYPES,
            FileType::OTHER => [],
        };

        if (!empty($allowedMimeTypes) && !\in_array($mimeType, $allowedMimeTypes, true)) {
            $errors[$field] = ['key' => 'general.file.invalidMimeType', 'params' => ['%allowed%' => implode(', ', $allowedMimeTypes)]];
        }

        $maxSize = match ($type) {
            FileType::IMAGE, FileType::OTHER => File::MAX_IMAGE_SIZE,
        };

        if ($size > $maxSize) {
            $errors[$field] = ['key' => 'general.file.tooBig', 'params' => ['%size%' => $maxSize / 1024 / 1024]];
        }

        return $errors;
    }

    /**
     * @return array<string, array{key: string, params?: array<string, int|null>}>
     */
    protected static function fieldRequired(string $field, int|string|float|null $value): array
    {
        $errors = [];
        if ($value === null || (\is_string($value) && mb_trim($value) === '')) {
            $errors[$field] = ['key' => 'general.string.required'];
        }

        return $errors;
    }

    /**
     * @return array<string, array{key: string, params?: array<string, int|null>}>
     */
    protected static function validateStringLength(string $field, ?string $string, ?int $min = null, ?int $max = null): array
    {
        $errors = [];
        $strLen = mb_strlen(mb_trim($string ?? ''));

        if ($min !== null && $strLen < $min) {
            $errors[$field] = ['key' => 'general.string.tooShort', 'params' => ['%min%' => $min]];
        }

        if ($max !== null && $strLen > $max) {
            $errors[$field] = ['key' => 'general.string.tooLong', 'params' => ['%max%' => $max]];
        }

        return $errors;
    }

    /**
     * @return array<string, array{key: string, params?: array<string, int|null>}>
     */
    protected static function validateIntegerValue(string $field, ?int $integer, ?int $min = null, ?int $max = null): array
    {
        $errors = [];

        if ($min !== null && $integer < $min) {
            $errors[$field] = ['key' => 'general.number.tooSmall', 'params' => ['%min%' => $min]];
        }

        if ($max !== null && $integer > $max) {
            $errors[$field] = ['key' => 'general.number.tooBig', 'params' => ['%max%' => $max]];
        }

        return $errors;
    }

    /**
     * @return array<string, array{key: string, params?: array<string, float|null>}>
     */
    protected static function validateFloatValue(string $field, ?float $float, ?float $min = null, ?float $max = null): array
    {
        $errors = [];

        if ($min !== null && $float < $min) {
            $errors[$field] = ['key' => 'general.number.tooSmall', 'params' => ['%min%' => $min]];
        }

        if ($max !== null && $float > $max) {
            $errors[$field] = ['key' => 'general.number.tooBig', 'params' => ['%max%' => $max]];
        }

        return $errors;
    }
}
