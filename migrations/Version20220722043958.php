<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220722043958 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client_xero_account DROP CONSTRAINT fk_d420da7ea76ed395');
        $this->addSql('DROP INDEX idx_d420da7ea76ed395');
        $this->addSql('ALTER TABLE client_xero_account RENAME COLUMN user_id TO client_id');
        $this->addSql('ALTER TABLE client_xero_account ADD CONSTRAINT FK_D420DA7E19EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_D420DA7E19EB6921 ON client_xero_account (client_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client_xero_account DROP CONSTRAINT FK_D420DA7E19EB6921');
        $this->addSql('DROP INDEX IDX_D420DA7E19EB6921');
        $this->addSql('ALTER TABLE client_xero_account RENAME COLUMN client_id TO user_id');
        $this->addSql('ALTER TABLE client_xero_account ADD CONSTRAINT fk_d420da7ea76ed395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_d420da7ea76ed395 ON client_xero_account (user_id)');
    }
}
