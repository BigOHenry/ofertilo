<?php

declare(strict_types=1);

namespace App\Domain\Product\Validator;

use App\Domain\Shared\File\ValueObject\FileType;
use App\Domain\Shared\Validator\Validator;
use App\Domain\Translation\DTO\TranslationDto;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProductValidator extends Validator
{
    public const int CODE_MIN_LENGTH = 4;
    public const int CODE_MAX_LENGTH = 100;
    public const int NAME_MIN_LENGTH = 10;
    public const int NAME_MAX_LENGTH = 300;
    public const null DESCRIPTION_MIN_LENGTH = null;
    public const int DESCRIPTION_MAX_LENGTH = 21000;
    public const null SHORT_DESCRIPTION_MIN_LENGTH = null;
    public const int SHORT_DESCRIPTION_MAX_LENGTH = 800;

    /**
     * @param TranslationDto[] $translations
     *
     * @return array<string, array{key: string, params?: array<string, float|int|string|null>}>
     */
    public static function validate(string $code, array $translations, ?UploadedFile $file): array
    {
        $errors = [];

        $fieldRules = [
            'name' => 'validateName',
            'short_description' => 'validateShortDescription',
            'description' => 'validateDescription',
        ];

        return array_merge(
            $errors,
            self::validateTranslatableFields($translations, $fieldRules),
            self::validateCode($code),
            self::validateUploadedFile('imageFile', $file, FileType::IMAGE)
        );
    }

    /**
     * @return array<string, array{key: string, params?: array<string, int|null>}>
     */
    protected static function validateCode(?string $value): array
    {
        $errors = self::validateStringLength('code', $value, self::CODE_MIN_LENGTH, self::CODE_MAX_LENGTH);

        if ($value !== null && !preg_match('/^[A-Za-z0-9._#-]+$/', $value)) {
            $errors['code'] = ['key' => 'product.code.invalid'];
        }

        return $errors;
    }

    /**
     * @return array<string, array{key: string, params?: array<string, int|null>}>
     */
    protected static function validateName(?string $value): array
    {
        return self::validateStringLength('name', $value, self::NAME_MIN_LENGTH, self::NAME_MAX_LENGTH);
    }

    /**
     * @return array<string, array{key: string, params?: array<string, int|null>}>
     */
    protected static function validateDescription(?string $value): array
    {
        return self::validateStringLength('description', $value, self::DESCRIPTION_MIN_LENGTH, self::DESCRIPTION_MAX_LENGTH);
    }

    /**
     * @return array<string, array{key: string, params?: array<string, int|null>}>
     */
    protected static function validateShortDescription(?string $value): array
    {
        return self::validateStringLength('short_description', $value, self::SHORT_DESCRIPTION_MIN_LENGTH, self::SHORT_DESCRIPTION_MAX_LENGTH);
    }
}
