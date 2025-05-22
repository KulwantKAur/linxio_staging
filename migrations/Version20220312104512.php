<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220312104512 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE file ADD original_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F3610108B7592 FOREIGN KEY (original_id) REFERENCES file (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8C9F3610108B7592 ON file (original_id)');
        $this->addSql('ALTER TABLE file ADD mime_type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE file ADD size BIGINT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE file DROP size');
        $this->addSql('ALTER TABLE file DROP mime_type');
        $this->addSql('ALTER TABLE file DROP CONSTRAINT FK_8C9F3610108B7592');
        $this->addSql('DROP INDEX UNIQ_8C9F3610108B7592');
        $this->addSql('ALTER TABLE file DROP original_id');
    }
}
