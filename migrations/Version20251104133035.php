<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251104133035 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE wood (id SERIAL NOT NULL, name VARCHAR(50) NOT NULL, latin_name VARCHAR(300) DEFAULT NULL, dry_density INT DEFAULT NULL, hardness INT DEFAULT NULL, enabled BOOLEAN DEFAULT true NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3C9D190D5E237E06 ON wood (name)');
        $this->addSql('ALTER TABLE material ADD wood_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE material DROP name');
        $this->addSql('ALTER TABLE material DROP latin_name');
        $this->addSql('ALTER TABLE material DROP dry_density');
        $this->addSql('ALTER TABLE material DROP hardness');
        $this->addSql('ALTER TABLE material ADD CONSTRAINT FK_7CBE75957B2710BE FOREIGN KEY (wood_id) REFERENCES wood (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_7CBE75957B2710BE ON material (wood_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE material DROP CONSTRAINT FK_7CBE75957B2710BE');
        $this->addSql('DROP TABLE wood');
        $this->addSql('DROP INDEX IDX_7CBE75957B2710BE');
        $this->addSql('ALTER TABLE material ADD name VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE material ADD latin_name VARCHAR(300) DEFAULT NULL');
        $this->addSql('ALTER TABLE material ADD hardness INT DEFAULT NULL');
        $this->addSql('ALTER TABLE material RENAME COLUMN wood_id TO dry_density');
    }
}
