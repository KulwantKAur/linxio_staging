<?php

namespace Application\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190412125925 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE entity_history ALTER email DROP NOT NULL');
        $this->addSql('DROP INDEX idx_a79c98c1a76ed395');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A79C98C1A76ED395 ON otp (user_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE entity_history ALTER email SET NOT NULL');
        $this->addSql('DROP INDEX UNIQ_A79C98C1A76ED395');
        $this->addSql('CREATE INDEX idx_a79c98c1a76ed395 ON otp (user_id)');
    }
}
