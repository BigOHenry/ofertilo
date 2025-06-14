<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250608185755 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE appuser ADD is_two_fa_enabled BOOLEAN DEFAULT false NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appuser ADD two_fa_secret VARCHAR(255) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appuser DROP is_two_fa_enabled
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appuser DROP two_fa_secret
        SQL);
    }
}
