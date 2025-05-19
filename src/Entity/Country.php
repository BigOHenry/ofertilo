<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

#[Entity]
class Country
{
    #[Id]
    #[GeneratedValue]
    #[Column]
    private ?int $id = null;

    #[Column(type: 'text', length: 200, unique: true)]
    private ?string $name = null;

    #[Column(type: 'text', length: 2, unique: true)]
    private ?string $alpha2 = null;

    #[Column(type: 'text', length: 3, unique: true)]
    private ?string $alpha3 = null;
}
