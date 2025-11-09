<?php

declare(strict_types=1);

namespace App\Domain\Color\Validator;

class ColorValidator
{
    /**
     * @return array<string, string>
     */
    public static function validateEmail(string $email): array
    {
        $errors = [];

        if (!filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        return $errors;
    }

    /**
     * @return array<string, string>
     */
    public static function validateName(string $name): array
    {
        $errors = [];

        if (mb_strlen(mb_trim($name)) < 2) {
            $errors['name'] = 'Name must be at least 2 characters long';
        }

        return $errors;
    }

    /**
     * @return array<string, string>
     */
    public static function validatePassword(string $password): array
    {
        $errors = [];

        if (
            mb_strlen($password) < 8
            || !preg_match('/[A-Z]/', $password)
            || !preg_match('/[a-z]/', $password)
            || !preg_match('/[0-9]/', $password)
            || !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)
        ) {
            $errors['password'] = 'Password must be at least 8 characters long, contains at least one'
                . ' lowercase and uppercase letter, one number and one special character.';
        }

        return $errors;
    }

    /**
     * @return array<string, string>
     */
    public static function validateForCreation(string $email, string $name, string $role, string $password): array
    {
        return array_merge(
            self::validateEmail(email: $email),
            self::validateName(name: $name),
            self::validatePassword(password: $password)
        );
    }

    /**
     * @return array<string, string>
     */
    public static function validateForUpdate(string $email, string $name, string $role): array
    {
        return array_merge(
            self::validateEmail(email: $email),
            self::validateName(name: $name),
        );
    }
}
