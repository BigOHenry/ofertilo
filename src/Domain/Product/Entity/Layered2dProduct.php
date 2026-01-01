<?php

declare(strict_types=1);

namespace App\Domain\Product\Entity;

use App\Domain\Product\ValueObject\ProductType;
use App\Domain\Shared\Country\Entity\Country;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Layered2dProduct extends Product
{
    public static function create(?Country $country, bool $enabled = true): self
    {
        $product = new self($country);
        $product->setEnabled($enabled);

        return $product;
    }

    public function getType(): ProductType
    {
        return ProductType::LAYERED_2D;
    }
}
