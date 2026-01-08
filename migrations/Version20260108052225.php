<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260108052225 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_component DROP CONSTRAINT fk_275e17dae308ac6f');
        $this->addSql('ALTER TABLE product_component DROP material_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_component ADD material_id VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE product_component ADD CONSTRAINT fk_275e17dae308ac6f FOREIGN KEY (material_id) REFERENCES material (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_275E17DAE308AC6F ON product_component (material_id)');
    }
}
