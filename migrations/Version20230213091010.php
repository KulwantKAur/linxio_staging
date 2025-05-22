<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230213091010 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE currency_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE currency (id SMALLINT NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, symbol VARCHAR(255) NOT NULL, decimals SMALLINT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6956883F77153098 ON currency (code)');

        $this->addSql('ALTER TABLE platform_setting ADD currency_id SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE platform_setting DROP currency');
        $this->addSql('ALTER TABLE platform_setting ADD CONSTRAINT FK_8A4472CA38248176 FOREIGN KEY (currency_id) REFERENCES currency (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_8A4472CA38248176 ON platform_setting (currency_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE platform_setting DROP CONSTRAINT FK_8A4472CA38248176');
        $this->addSql('DROP SEQUENCE currency_id_seq CASCADE');
        $this->addSql('DROP TABLE currency');
        $this->addSql('DROP INDEX IDX_8A4472CA38248176');
        $this->addSql('ALTER TABLE platform_setting ADD currency VARCHAR(30) DEFAULT NULL');
        $this->addSql('ALTER TABLE platform_setting DROP currency_id');
    }
}
