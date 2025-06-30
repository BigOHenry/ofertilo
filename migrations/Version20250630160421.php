<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250630160421 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE appuser ALTER email TYPE VARCHAR(255)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appuser ALTER password TYPE VARCHAR(200)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appuser ALTER force_password_change SET DEFAULT false
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appuser ALTER force_email_change SET DEFAULT false
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appuser RENAME COLUMN is_two_fa_enabled TO two_fa_enabled
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE material ALTER name TYPE VARCHAR(50)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX unique_material_thickness ON material_price (material_id, thickness)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_color ALTER description TYPE VARCHAR(500)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_color ALTER description TYPE TEXT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_color ALTER description TYPE TEXT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE material ALTER name TYPE VARCHAR(200)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX unique_material_thickness
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appuser ALTER password TYPE VARCHAR(255)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appuser ALTER force_password_change DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appuser ALTER force_email_change DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appuser ALTER email TYPE VARCHAR(180)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appuser RENAME COLUMN two_fa_enabled TO is_two_fa_enabled
        SQL);
    }
}
