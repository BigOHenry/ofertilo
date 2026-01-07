<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Domain\Color\Entity\Color;
use App\Domain\Material\Entity\EdgeGluedPanelMaterial;
use App\Domain\Material\Entity\Material;
use App\Domain\Material\Entity\PieceMaterial;
use App\Domain\Material\Entity\PlywoodMaterial;
use App\Domain\Material\Entity\SolidWoodMaterial;
use App\Domain\Product\Entity\FlagProduct;
use App\Domain\Product\Entity\Layered2dProduct;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Entity\Relief3dProduct;
use App\Domain\Shared\Country\Entity\Country;
use App\Domain\Wood\Entity\Wood;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Uuid;

final class Version20260107132913 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migrate primary keys from integer to UUID (VARCHAR 36) with data preservation';
    }

    public function up(Schema $schema): void
    {
        // STEP 1: Add temporary UUID columns for all tables
        $this->addSql('ALTER TABLE appuser ADD COLUMN new_id VARCHAR(36)');
        $this->addSql('ALTER TABLE client ADD COLUMN new_id VARCHAR(36)');
        $this->addSql('ALTER TABLE color ADD COLUMN new_id VARCHAR(36)');
        $this->addSql('ALTER TABLE country ADD COLUMN new_id VARCHAR(36)');
        $this->addSql('ALTER TABLE material ADD COLUMN new_id VARCHAR(36)');
        $this->addSql('ALTER TABLE material_price ADD COLUMN new_id VARCHAR(36)');
        $this->addSql('ALTER TABLE product ADD COLUMN new_id VARCHAR(36)');
        $this->addSql('ALTER TABLE product_color ADD COLUMN new_id VARCHAR(36)');
        $this->addSql('ALTER TABLE product_component ADD COLUMN new_id VARCHAR(36)');
        $this->addSql('ALTER TABLE product_size ADD COLUMN new_id VARCHAR(36)');
        $this->addSql('ALTER TABLE translation ADD COLUMN new_id VARCHAR(36)');
        $this->addSql('ALTER TABLE wood ADD COLUMN new_id VARCHAR(36)');
        $this->addSql('ALTER TABLE translation ADD COLUMN new_object_id VARCHAR(255)');

        // STEP 2: Generate UUIDs for all records
        $this->generateUuids('appuser');
        $this->generateUuids('client');
        $this->generateUuids('color');
        $this->generateUuids('country');
        $this->generateUuids('material');
        $this->generateUuids('material_price');
        $this->generateUuids('product');
        $this->generateUuids('product_color');
        $this->generateUuids('product_component');
        $this->generateUuids('product_size');
        $this->generateUuids('translation');
        $this->generateUuids('wood');

        // STEP 3: Add temporary FK columns
        $this->addSql('ALTER TABLE client ADD COLUMN new_country_id VARCHAR(36)');
        $this->addSql('ALTER TABLE material ADD COLUMN new_wood_id VARCHAR(36)');
        $this->addSql('ALTER TABLE material_price ADD COLUMN new_material_id VARCHAR(36)');
        $this->addSql('ALTER TABLE product ADD COLUMN new_country_id VARCHAR(36)');
        $this->addSql('ALTER TABLE product_color ADD COLUMN new_product_id VARCHAR(36)');
        $this->addSql('ALTER TABLE product_color ADD COLUMN new_color_id VARCHAR(36)');
        $this->addSql('ALTER TABLE product_component ADD COLUMN new_product_size_id VARCHAR(36)');
        $this->addSql('ALTER TABLE product_component ADD COLUMN new_material_id VARCHAR(36)');
        $this->addSql('ALTER TABLE product_size ADD COLUMN new_product_id VARCHAR(36)');

        // STEP 4: Map FK relationships from old IDs to new UUIDs
        $this->addSql('UPDATE client c SET new_country_id = (SELECT new_id FROM country WHERE id = c.country_id)');
        $this->addSql('UPDATE material m SET new_wood_id = (SELECT new_id FROM wood WHERE id = m.wood_id)');
        $this->addSql('UPDATE material_price mp SET new_material_id = (SELECT new_id FROM material WHERE id = mp.material_id)');
        $this->addSql('UPDATE product p SET new_country_id = (SELECT new_id FROM country WHERE id = p.country_id)');
        $this->addSql('UPDATE product_color pc SET new_product_id = (SELECT new_id FROM product WHERE id = pc.product_id)');
        $this->addSql('UPDATE product_color pc SET new_color_id = (SELECT new_id FROM color WHERE id = pc.color_id)');
        $this->addSql('UPDATE product_component pc SET new_product_size_id = (SELECT new_id FROM product_size WHERE id = pc.product_size_id)');
        $this->addSql('UPDATE product_component pc SET new_material_id = (SELECT new_id FROM material WHERE id = pc.material_id) WHERE pc.material_id IS NOT NULL');
        $this->addSql('UPDATE product_size ps SET new_product_id = (SELECT new_id FROM product WHERE id = ps.product_id)');

        // STEP 4b: Remap translation.object_id to the new UUID
        $this->migrateTranslationObjectIds(Product::class, 'product');
        $this->migrateTranslationObjectIds(FlagProduct::class, 'product');
        $this->migrateTranslationObjectIds(Relief3dProduct::class, 'product');
        $this->migrateTranslationObjectIds(Layered2dProduct::class, 'product');
        $this->migrateTranslationObjectIds(Color::class, 'color');
        $this->migrateTranslationObjectIds(Country::class, 'country');
        $this->migrateTranslationObjectIds(Wood::class, 'wood');
        $this->migrateTranslationObjectIds(Material::class, 'material');
        $this->migrateTranslationObjectIds(PieceMaterial::class, 'material');
        $this->migrateTranslationObjectIds(PlywoodMaterial::class, 'material');
        $this->migrateTranslationObjectIds(EdgeGluedPanelMaterial::class, 'material');
        $this->migrateTranslationObjectIds(SolidWoodMaterial::class, 'material');

        // STEP 5: Drop ALL foreign key constraints (using actual constraint names from database)
        $this->dropAllForeignKeys('client');
        $this->dropAllForeignKeys('material');
        $this->dropAllForeignKeys('material_price');
        $this->dropAllForeignKeys('product');
        $this->dropAllForeignKeys('product_color');
        $this->dropAllForeignKeys('product_component');
        $this->dropAllForeignKeys('product_size');

        // STEP 6: Drop old PK constraints
        $this->addSql('ALTER TABLE appuser DROP CONSTRAINT IF EXISTS appuser_pkey CASCADE');
        $this->addSql('ALTER TABLE client DROP CONSTRAINT IF EXISTS client_pkey CASCADE');
        $this->addSql('ALTER TABLE color DROP CONSTRAINT IF EXISTS color_pkey CASCADE');
        $this->addSql('ALTER TABLE country DROP CONSTRAINT IF EXISTS country_pkey CASCADE');
        $this->addSql('ALTER TABLE material DROP CONSTRAINT IF EXISTS material_pkey CASCADE');
        $this->addSql('ALTER TABLE material_price DROP CONSTRAINT IF EXISTS material_price_pkey CASCADE');
        $this->addSql('ALTER TABLE product DROP CONSTRAINT IF EXISTS product_pkey CASCADE');
        $this->addSql('ALTER TABLE product_color DROP CONSTRAINT IF EXISTS product_color_pkey CASCADE');
        $this->addSql('ALTER TABLE product_component DROP CONSTRAINT IF EXISTS product_component_pkey CASCADE');
        $this->addSql('ALTER TABLE product_size DROP CONSTRAINT IF EXISTS product_size_pkey CASCADE');
        $this->addSql('ALTER TABLE translation DROP CONSTRAINT IF EXISTS translation_pkey CASCADE');
        $this->addSql('ALTER TABLE wood DROP CONSTRAINT IF EXISTS wood_pkey CASCADE');

        // Drop sequences
        $this->addSql('DROP SEQUENCE IF EXISTS appuser_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS client_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS color_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS country_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS material_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS material_price_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS product_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS product_color_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS translation_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS wood_id_seq CASCADE');

        // STEP 7: Drop old columns
        $this->addSql('ALTER TABLE appuser DROP COLUMN id');
        $this->addSql('ALTER TABLE client DROP COLUMN id, DROP COLUMN country_id');
        $this->addSql('ALTER TABLE color DROP COLUMN id');
        $this->addSql('ALTER TABLE country DROP COLUMN id');
        $this->addSql('ALTER TABLE material DROP COLUMN id, DROP COLUMN wood_id');
        $this->addSql('ALTER TABLE material_price DROP COLUMN id, DROP COLUMN material_id');
        $this->addSql('ALTER TABLE product DROP COLUMN id, DROP COLUMN country_id');
        $this->addSql('ALTER TABLE product_color DROP COLUMN id, DROP COLUMN product_id, DROP COLUMN color_id');
        $this->addSql('ALTER TABLE product_component DROP COLUMN id, DROP COLUMN product_size_id, DROP COLUMN material_id');
        $this->addSql('ALTER TABLE product_size DROP COLUMN id, DROP COLUMN product_id');
        $this->addSql('ALTER TABLE translation DROP COLUMN id, DROP COLUMN object_id');
        $this->addSql('ALTER TABLE wood DROP COLUMN id');

        // STEP 8: Rename new columns to original names
        $this->addSql('ALTER TABLE appuser RENAME COLUMN new_id TO id');
        $this->addSql('ALTER TABLE client RENAME COLUMN new_id TO id');
        $this->addSql('ALTER TABLE client RENAME COLUMN new_country_id TO country_id');
        $this->addSql('ALTER TABLE color RENAME COLUMN new_id TO id');
        $this->addSql('ALTER TABLE country RENAME COLUMN new_id TO id');
        $this->addSql('ALTER TABLE material RENAME COLUMN new_id TO id');
        $this->addSql('ALTER TABLE material RENAME COLUMN new_wood_id TO wood_id');
        $this->addSql('ALTER TABLE material_price RENAME COLUMN new_id TO id');
        $this->addSql('ALTER TABLE material_price RENAME COLUMN new_material_id TO material_id');
        $this->addSql('ALTER TABLE product RENAME COLUMN new_id TO id');
        $this->addSql('ALTER TABLE product RENAME COLUMN new_country_id TO country_id');
        $this->addSql('ALTER TABLE product_color RENAME COLUMN new_id TO id');
        $this->addSql('ALTER TABLE product_color RENAME COLUMN new_product_id TO product_id');
        $this->addSql('ALTER TABLE product_color RENAME COLUMN new_color_id TO color_id');
        $this->addSql('ALTER TABLE product_component RENAME COLUMN new_id TO id');
        $this->addSql('ALTER TABLE product_component RENAME COLUMN new_product_size_id TO product_size_id');
        $this->addSql('ALTER TABLE product_component RENAME COLUMN new_material_id TO material_id');
        $this->addSql('ALTER TABLE product_size RENAME COLUMN new_id TO id');
        $this->addSql('ALTER TABLE product_size RENAME COLUMN new_product_id TO product_id');
        $this->addSql('ALTER TABLE translation RENAME COLUMN new_id TO id');
        $this->addSql('ALTER TABLE translation RENAME COLUMN new_object_id TO object_id');
        $this->addSql('ALTER TABLE wood RENAME COLUMN new_id TO id');

        // STEP 9: Set NOT NULL for new PKs and create PK constraints
        $this->addSql('ALTER TABLE appuser ALTER COLUMN id SET NOT NULL');
        $this->addSql('ALTER TABLE client ALTER COLUMN id SET NOT NULL');
        $this->addSql('ALTER TABLE color ALTER COLUMN id SET NOT NULL');
        $this->addSql('ALTER TABLE country ALTER COLUMN id SET NOT NULL');
        $this->addSql('ALTER TABLE material ALTER COLUMN id SET NOT NULL');
        $this->addSql('ALTER TABLE material_price ALTER COLUMN id SET NOT NULL');
        $this->addSql('ALTER TABLE product ALTER COLUMN id SET NOT NULL');
        $this->addSql('ALTER TABLE product_color ALTER COLUMN id SET NOT NULL');
        $this->addSql('ALTER TABLE product_component ALTER COLUMN id SET NOT NULL');
        $this->addSql('ALTER TABLE product_size ALTER COLUMN id SET NOT NULL');
        $this->addSql('ALTER TABLE translation ALTER COLUMN id SET NOT NULL');
        $this->addSql('ALTER TABLE wood ALTER COLUMN id SET NOT NULL');

        $this->addSql('ALTER TABLE appuser ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE client ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE color ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE country ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE material ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE material_price ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE product ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE product_color ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE product_component ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE product_size ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE translation ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE wood ADD PRIMARY KEY (id)');

        // STEP 10: Create new FK constraints
        $this->addSql('ALTER TABLE client ADD CONSTRAINT fk_client_country FOREIGN KEY (country_id) REFERENCES country(id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE material ADD CONSTRAINT fk_material_wood FOREIGN KEY (wood_id) REFERENCES wood(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE material_price ADD CONSTRAINT fk_material_price_material FOREIGN KEY (material_id) REFERENCES material(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT fk_product_country FOREIGN KEY (country_id) REFERENCES country(id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE product_color ADD CONSTRAINT fk_product_color_product FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_color ADD CONSTRAINT fk_product_color_color FOREIGN KEY (color_id) REFERENCES color(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_component ADD CONSTRAINT fk_product_component_product_size FOREIGN KEY (product_size_id) REFERENCES product_size(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_component ADD CONSTRAINT fk_product_component_material FOREIGN KEY (material_id) REFERENCES material(id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE product_size ADD CONSTRAINT fk_product_size_product FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Cannot revert UUID to integer IDs - data loss would occur');
    }

    private function generateUuids(string $table): void
    {
        $connection = $this->connection;
        $rows = $connection->fetchAllAssociative("SELECT id FROM {$table}");

        foreach ($rows as $row) {
            $uuid = Uuid::uuid4()->toString();
            $this->addSql("UPDATE {$table} SET new_id = :uuid WHERE id = :id", [
                'uuid' => $uuid,
                'id' => $row['id'],
            ]);
        }
    }

    private function migrateTranslationObjectIds(string $objectClass, string $referencedTable): void
    {
        $this->addSql("
            UPDATE translation t
            SET new_object_id = (
                SELECT new_id 
                FROM {$referencedTable} 
                WHERE id = t.object_id
            )
            WHERE t.object_class = :objectClass
            AND t.object_id IS NOT NULL
        ", ['objectClass' => $objectClass]);
    }

    private function dropAllForeignKeys(string $table): void
    {
        // Dynamicky získat všechny FK constraints pro danou tabulku
        $sql = "
            SELECT constraint_name 
            FROM information_schema.table_constraints 
            WHERE table_name = :table 
            AND constraint_type = 'FOREIGN KEY'
        ";

        $constraints = $this->connection->fetchAllAssociative($sql, ['table' => $table]);

        foreach ($constraints as $constraint) {
            $constraintName = $constraint['constraint_name'];
            $this->addSql("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$constraintName}");
        }
    }
}
