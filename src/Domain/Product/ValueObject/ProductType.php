<?php

declare(strict_types=1);

namespace App\Domain\Product\ValueObject;

use App\Domain\Product\Entity\FlagProduct;
use App\Domain\Product\Entity\Layered2dProduct;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Entity\Relief3dProduct;

enum ProductType: string
{
    case FLAG = 'flag';
    case RELIEF_3D = 'relief_3d';
    case LAYERED_2D = 'layered_2d';

    /** @var array<value-of<self>, class-string<Product>> */
    public const array DISCRIMINATOR_MAP = [
        self::FLAG->value => FlagProduct::class,
        self::RELIEF_3D->value => Relief3dProduct::class,
        self::LAYERED_2D->value => Layered2dProduct::class,
    ];

    public function label(): string
    {
        return 'product.type.' . $this->value;
    }
}
