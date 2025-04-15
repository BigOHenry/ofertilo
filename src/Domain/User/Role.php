<?php

namespace App\Domain\User;

enum Role: string
{
    case SUPER_ADMIN = 'SUPER_ADMIN';
    case ADMIN = 'ADMIN';
    case WRITER = 'WRITER';
    case READER = 'READER';
}