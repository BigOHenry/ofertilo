<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260108050737 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_size RENAME TO product_variant');
        $this->addSql('ALTER TABLE product_component DROP CONSTRAINT fk_275e17da9854b397');
        $this->addSql('ALTER TABLE product_component ADD blueprint_file_id VARCHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE product_component DROP blueprint_image');
        $this->addSql('ALTER TABLE product_component DROP blueprint_original_name');
        $this->addSql('ALTER TABLE product_component DROP blueprint_mime_type');
        $this->addSql('ALTER TABLE product_component ADD CONSTRAINT FK_275E17DA9854B397 FOREIGN KEY (product_size_id) REFERENCES product_variant (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE product_component ADD CONSTRAINT FK_275E17DA742CC768 FOREIGN KEY (blueprint_file_id) REFERENCES file (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_275E17DA742CC768 ON product_component (blueprint_file_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_variant RENAME TO product_size');
        $this->addSql('ALTER TABLE product_component DROP CONSTRAINT FK_275E17DA9854B397');
        $this->addSql('ALTER TABLE product_component DROP CONSTRAINT FK_275E17DA742CC768');
        $this->addSql('DROP INDEX UNIQ_275E17DA742CC768');
        $this->addSql('ALTER TABLE product_component ADD blueprint_image BYTEA DEFAULT NULL');
        $this->addSql('ALTER TABLE product_component ADD blueprint_original_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE product_component ADD blueprint_mime_type VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE product_component DROP blueprint_file_id');
        $this->addSql('ALTER TABLE product_component ADD CONSTRAINT fk_275e17da9854b397 FOREIGN KEY (product_size_id) REFERENCES product_size (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
