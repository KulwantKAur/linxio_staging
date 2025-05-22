<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220916151936 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE invoice ADD prepayment_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE invoice ADD type VARCHAR(255) NOT NULL DEFAULT \'regular\'');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744BB3BD4DA FOREIGN KEY (prepayment_id) REFERENCES invoice (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_90651744BB3BD4DA ON invoice (prepayment_id)');
        $this->addSql('ALTER TABLE invoice ADD previous_prepayment_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744B3FB3CB5 FOREIGN KEY (previous_prepayment_id) REFERENCES invoice (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT FK_90651744BB3BD4DA');
        $this->addSql('DROP INDEX UNIQ_90651744BB3BD4DA');
        $this->addSql('ALTER TABLE invoice DROP prepayment_id');
        $this->addSql('ALTER TABLE invoice DROP type');
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT FK_90651744B3FB3CB5');
        $this->addSql('ALTER TABLE invoice DROP previous_prepayment_id');
    }
}
