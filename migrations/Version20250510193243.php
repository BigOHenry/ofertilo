<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250510193243 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE material_price (id SERIAL NOT NULL, material_id INT NOT NULL, thickness INT NOT NULL, price NUMERIC(10, 2) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9C2F8E97E308AC6F ON material_price (material_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE material_price ADD CONSTRAINT FK_9C2F8E97E308AC6F FOREIGN KEY (material_id) REFERENCES material (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appuser ALTER roles TYPE JSONB
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE material_price DROP CONSTRAINT FK_9C2F8E97E308AC6F
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE material_price
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appuser ALTER roles TYPE JSONB
        SQL);
    }
}
