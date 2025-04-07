<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity]
class Client
{
    #[Id]
    #[GeneratedValue]
    #[Column]
    private ?int $id = null;

    #[Column(length: 400, nullable: true)]
    private ?string $name = null;

    #[Column(length: 400, nullable: true)]
    private ?string $company = null;

    #[Column(length: 255, unique: true)]
    private ?string $email = null;

    #[Column(length: 20, nullable: true)]
    private ?string $phone = null;

    #[Column(length: 400, nullable: true)]
    private ?string $instagram = null;

    #[Column(length: 400, nullable: true)]
    private ?string $facebook = null;

    #[ManyToOne]
    #[JoinColumn(nullable: true)]
    private ?Country $country = null;

    #[Column]
    private ?\DateTimeImmutable $createdAt = null;
}