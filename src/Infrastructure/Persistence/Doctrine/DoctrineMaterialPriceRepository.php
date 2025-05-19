<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Material\MaterialPrice;
use App\Domain\Material\MaterialPriceRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineMaterialPriceRepository implements MaterialPriceRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function save(MaterialPrice $price): void
    {
        $this->em->persist($price);
        $this->em->flush();
    }

    public function remove(MaterialPrice $price): void
    {
        $this->em->remove($price);
        $this->em->flush();
    }
}
