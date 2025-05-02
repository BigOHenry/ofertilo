<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250502133228 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE material (id SERIAL NOT NULL, name VARCHAR(200) NOT NULL, description VARCHAR(400) NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appuser ALTER roles TYPE JSONB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX uniq_8d93d649e7927c74 RENAME TO UNIQ_EE8A7C74E7927C74
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE material
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appuser ALTER roles TYPE JSONB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX uniq_ee8a7c74e7927c74 RENAME TO uniq_8d93d649e7927c74
        SQL);
    }
}
