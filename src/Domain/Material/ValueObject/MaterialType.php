<?php

declare(strict_types=1);

namespace App\Domain\Material\ValueObject;

use App\Domain\Material\Entity\EdgeGluedPanelMaterial;
use App\Domain\Material\Entity\Material;
use App\Domain\Material\Entity\PieceMaterial;
use App\Domain\Material\Entity\PlywoodMaterial;
use App\Domain\Material\Entity\SolidWoodMaterial;

enum MaterialType: string
{
    case PIECE = 'piece';                      // kusový
    case PLYWOOD = 'plywood';                  // překližka
    case EDGE_GLUED_PANEL = 'edge_glued_panel'; // spárovka
    case SOLID_WOOD = 'solid_wood';            // masivní dřevo

    /** @var array<value-of<self>, class-string<Material>> */
    public const array DISCRIMINATOR_MAP = [
        self::PIECE->value => PieceMaterial::class,
        self::PLYWOOD->value => PlywoodMaterial::class,
        self::EDGE_GLUED_PANEL->value => EdgeGluedPanelMaterial::class,
        self::SOLID_WOOD->value => SolidWoodMaterial::class,
    ];

    public function label(): string
    {
        return 'material.type.' . $this->value;
    }
}
