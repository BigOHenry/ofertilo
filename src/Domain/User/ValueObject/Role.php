<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

enum Role: string
{
    case SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    case ADMIN = 'ROLE_ADMIN';
    case WRITER = 'ROLE_WRITER';
    case READER = 'ROLE_READER';
}
