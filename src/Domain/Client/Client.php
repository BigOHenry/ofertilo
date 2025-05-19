<?php

declare(strict_types=1);

namespace App\Domain\Client;

use App\Entity\Country;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 400, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 400, nullable: true)]
    private ?string $company = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 400, nullable: true)]
    private ?string $instagram = null;

    #[ORM\Column(length: 400, nullable: true)]
    private ?string $facebook = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Country $country = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;
}
