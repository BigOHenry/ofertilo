<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250409201225 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE client (id SERIAL NOT NULL, country_id INT DEFAULT NULL, name VARCHAR(400) DEFAULT NULL, company VARCHAR(400) DEFAULT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(20) DEFAULT NULL, instagram VARCHAR(400) DEFAULT NULL, facebook VARCHAR(400) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_C7440455E7927C74 ON client (email)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C7440455F92F3E70 ON client (country_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN client.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE country (id SERIAL NOT NULL, name TEXT NOT NULL, alpha2 TEXT NOT NULL, alpha3 TEXT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_5373C9665E237E06 ON country (name)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_5373C966B762D672 ON country (alpha2)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_5373C966C065E6E4 ON country (alpha3)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE "group" (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_6DC044C55E237E06 ON "group" (name)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE group_permission (group_id INT NOT NULL, permission_id INT NOT NULL, PRIMARY KEY(group_id, permission_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3784F318FE54D947 ON group_permission (group_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3784F318FED90CCA ON group_permission (permission_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE permission (id SERIAL NOT NULL, code VARCHAR(100) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_E04992AA77153098 ON permission (code)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE "user" (id SERIAL NOT NULL, email VARCHAR(180) NOT NULL, name VARCHAR(200) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, needs_password_change BOOLEAN NOT NULL, force_email_change BOOLEAN NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_group (user_id INT NOT NULL, group_id INT NOT NULL, PRIMARY KEY(user_id, group_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8F02BF9DA76ED395 ON user_group (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8F02BF9DFE54D947 ON user_group (group_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client ADD CONSTRAINT FK_C7440455F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE group_permission ADD CONSTRAINT FK_3784F318FE54D947 FOREIGN KEY (group_id) REFERENCES "group" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE group_permission ADD CONSTRAINT FK_3784F318FED90CCA FOREIGN KEY (permission_id) REFERENCES permission (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_group ADD CONSTRAINT FK_8F02BF9DA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_group ADD CONSTRAINT FK_8F02BF9DFE54D947 FOREIGN KEY (group_id) REFERENCES "group" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);

        $this->addSql("
                INSERT INTO country (alpha2, alpha3, name) VALUES
                ('AL', 'ALB', 'Albánie'),
                ('AD', 'AND', 'Andorra'),
                ('AM', 'ARM', 'Arménie'),
                ('AT', 'AUT', 'Rakousko'),
                ('AZ', 'AZE', 'Ázerbájdžán'),
                ('BY', 'BLR', 'Bělorusko'),
                ('BE', 'BEL', 'Belgie'),
                ('BA', 'BIH', 'Bosna a Hercegovina'),
                ('BG', 'BGR', 'Bulharsko'),
                ('HR', 'HRV', 'Chorvatsko'),
                ('CY', 'CYP', 'Kypr'),
                ('CZ', 'CZE', 'Česká republika'),
                ('DK', 'DNK', 'Dánsko'),
                ('EE', 'EST', 'Estonsko'),
                ('FI', 'FIN', 'Finsko'),
                ('FR', 'FRA', 'Francie'),
                ('GE', 'GEO', 'Gruzie'),
                ('DE', 'DEU', 'Německo'),
                ('GR', 'GRC', 'Řecko'),
                ('HU', 'HUN', 'Maďarsko'),
                ('IS', 'ISL', 'Island'),
                ('IE', 'IRL', 'Irsko'),
                ('IT', 'ITA', 'Itálie'),
                ('KZ', 'KAZ', 'Kazachstán'),
                ('XK', 'XKX', 'Kosovo'),
                ('LV', 'LVA', 'Lotyšsko'),
                ('LI', 'LIE', 'Lichtenštejnsko'),
                ('LT', 'LTU', 'Litva'),
                ('LU', 'LUX', 'Lucembursko'),
                ('MT', 'MLT', 'Malta'),
                ('MD', 'MDA', 'Moldavsko'),
                ('MC', 'MCO', 'Monako'),
                ('ME', 'MNE', 'Černá Hora'),
                ('NL', 'NLD', 'Nizozemsko'),
                ('MK', 'MKD', 'Severní Makedonie'),
                ('NO', 'NOR', 'Norsko'),
                ('PL', 'POL', 'Polsko'),
                ('PT', 'PRT', 'Portugalsko'),
                ('RO', 'ROU', 'Rumunsko'),
                ('RU', 'RUS', 'Rusko'),
                ('SM', 'SMR', 'San Marino'),
                ('RS', 'SRB', 'Srbsko'),
                ('SK', 'SVK', 'Slovensko'),
                ('SI', 'SVN', 'Slovinsko'),
                ('ES', 'ESP', 'Španělsko'),
                ('SE', 'SWE', 'Švédsko'),
                ('CH', 'CHE', 'Švýcarsko'),
                ('TR', 'TUR', 'Turecko'),
                ('UA', 'UKR', 'Ukrajina'),
                ('GB', 'GBR', 'Velká Británie'),
                ('VA', 'VAT', 'Vatikán'),
                ('US', 'USA', 'Spojené státy')
            ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client DROP CONSTRAINT FK_C7440455F92F3E70
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE group_permission DROP CONSTRAINT FK_3784F318FE54D947
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE group_permission DROP CONSTRAINT FK_3784F318FED90CCA
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_group DROP CONSTRAINT FK_8F02BF9DA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_group DROP CONSTRAINT FK_8F02BF9DFE54D947
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE client
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE country
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE "group"
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE group_permission
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE permission
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE "user"
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_group
        SQL);
    }
}
