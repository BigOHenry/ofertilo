<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260108051206 extends AbstractMigration
{

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_component DROP CONSTRAINT fk_275e17da9854b397');
        $this->addSql('ALTER TABLE product_component RENAME COLUMN product_size_id TO product_variant_id');
        $this->addSql('ALTER TABLE product_component ADD CONSTRAINT FK_275E17DAA80EF684 FOREIGN KEY (product_variant_id) REFERENCES product_variant (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_275E17DAA80EF684 ON product_component (product_variant_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_component DROP CONSTRAINT FK_275E17DAA80EF684');
        $this->addSql('ALTER TABLE product_component RENAME COLUMN product_variant_id TO product_size_id');
        $this->addSql('ALTER TABLE product_component ADD CONSTRAINT fk_275e17da9854b397 FOREIGN KEY (product_size_id) REFERENCES product_variant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_275E17DA9854B397 ON product_component (product_size_id)');
    }
}
