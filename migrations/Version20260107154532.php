<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260107154532 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE client DROP CONSTRAINT fk_client_country');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE material DROP CONSTRAINT fk_material_wood');
        $this->addSql('ALTER TABLE material ADD CONSTRAINT FK_7CBE75957B2710BE FOREIGN KEY (wood_id) REFERENCES wood (id)');
        $this->addSql('ALTER TABLE material_price DROP CONSTRAINT fk_material_price_material');
        $this->addSql('ALTER TABLE material_price ALTER material_id SET NOT NULL');
        $this->addSql('ALTER TABLE material_price ADD CONSTRAINT FK_9C2F8E97E308AC6F FOREIGN KEY (material_id) REFERENCES material (id) NOT DEFERRABLE');
        $this->addSql('CREATE UNIQUE INDEX unique_material_thickness ON material_price (material_id, thickness)');
        $this->addSql('ALTER TABLE product DROP CONSTRAINT fk_product_country');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADF92F3E70 FOREIGN KEY (country_id) REFERENCES country (id) NOT DEFERRABLE');
        $this->addSql('CREATE UNIQUE INDEX unique_product_type_country ON product (type, country_id)');
        $this->addSql('ALTER TABLE product_color DROP CONSTRAINT fk_product_color_product');
        $this->addSql('ALTER TABLE product_color DROP CONSTRAINT fk_product_color_color');
        $this->addSql('ALTER TABLE product_color ALTER product_id SET NOT NULL');
        $this->addSql('ALTER TABLE product_color ALTER color_id SET NOT NULL');
        $this->addSql('ALTER TABLE product_color ADD CONSTRAINT FK_C70A33B54584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE product_color ADD CONSTRAINT FK_C70A33B57ADA1FB5 FOREIGN KEY (color_id) REFERENCES color (id) NOT DEFERRABLE');
        $this->addSql('CREATE UNIQUE INDEX unique_product_color ON product_color (product_id, color_id)');
        $this->addSql('ALTER TABLE product_component DROP CONSTRAINT fk_product_component_product_size');
        $this->addSql('ALTER TABLE product_component DROP CONSTRAINT fk_product_component_material');
        $this->addSql('ALTER TABLE product_component ALTER product_size_id SET NOT NULL');
        $this->addSql('ALTER TABLE product_component ALTER material_id SET NOT NULL');
        $this->addSql('ALTER TABLE product_component ADD CONSTRAINT FK_275E17DA9854B397 FOREIGN KEY (product_size_id) REFERENCES product_size (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE product_component ADD CONSTRAINT FK_275E17DAE308AC6F FOREIGN KEY (material_id) REFERENCES material (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE product_size DROP CONSTRAINT fk_product_size_product');
        $this->addSql('ALTER TABLE product_size ALTER product_id SET NOT NULL');
        $this->addSql('ALTER TABLE product_size ADD CONSTRAINT FK_7A2806CB4584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE translation ALTER object_id SET NOT NULL');
        $this->addSql('CREATE INDEX idx_translation_lookup ON translation (object_class, object_id, locale)');
        $this->addSql('CREATE UNIQUE INDEX uniq_translation_lookup ON translation (object_class, object_id, locale, field)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE client DROP CONSTRAINT FK_C7440455F92F3E70');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT fk_client_country FOREIGN KEY (country_id) REFERENCES country (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE material DROP CONSTRAINT FK_7CBE75957B2710BE');
        $this->addSql('ALTER TABLE material ADD CONSTRAINT fk_material_wood FOREIGN KEY (wood_id) REFERENCES wood (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE material_price DROP CONSTRAINT FK_9C2F8E97E308AC6F');
        $this->addSql('DROP INDEX unique_material_thickness');
        $this->addSql('ALTER TABLE material_price ALTER material_id DROP NOT NULL');
        $this->addSql('ALTER TABLE material_price ADD CONSTRAINT fk_material_price_material FOREIGN KEY (material_id) REFERENCES material (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product DROP CONSTRAINT FK_D34A04ADF92F3E70');
        $this->addSql('DROP INDEX unique_product_type_country');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT fk_product_country FOREIGN KEY (country_id) REFERENCES country (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_color DROP CONSTRAINT FK_C70A33B54584665A');
        $this->addSql('ALTER TABLE product_color DROP CONSTRAINT FK_C70A33B57ADA1FB5');
        $this->addSql('DROP INDEX unique_product_color');
        $this->addSql('ALTER TABLE product_color ALTER product_id DROP NOT NULL');
        $this->addSql('ALTER TABLE product_color ALTER color_id DROP NOT NULL');
        $this->addSql('ALTER TABLE product_color ADD CONSTRAINT fk_product_color_product FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_color ADD CONSTRAINT fk_product_color_color FOREIGN KEY (color_id) REFERENCES color (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_component DROP CONSTRAINT FK_275E17DA9854B397');
        $this->addSql('ALTER TABLE product_component DROP CONSTRAINT FK_275E17DAE308AC6F');
        $this->addSql('ALTER TABLE product_component ALTER product_size_id DROP NOT NULL');
        $this->addSql('ALTER TABLE product_component ALTER material_id DROP NOT NULL');
        $this->addSql('ALTER TABLE product_component ADD CONSTRAINT fk_product_component_product_size FOREIGN KEY (product_size_id) REFERENCES product_size (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_component ADD CONSTRAINT fk_product_component_material FOREIGN KEY (material_id) REFERENCES material (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_size DROP CONSTRAINT FK_7A2806CB4584665A');
        $this->addSql('ALTER TABLE product_size ALTER product_id DROP NOT NULL');
        $this->addSql('ALTER TABLE product_size ADD CONSTRAINT fk_product_size_product FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP INDEX idx_translation_lookup');
        $this->addSql('DROP INDEX uniq_translation_lookup');
        $this->addSql('ALTER TABLE translation ALTER object_id DROP NOT NULL');
    }
}
