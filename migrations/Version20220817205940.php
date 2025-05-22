<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220817205940 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE invoice_details RENAME COLUMN amount TO quantity;');
        $this->addSql('ALTER TABLE invoice_details RENAME CONSTRAINT id TO invoice_details_invoice_id;');
        $this->addSql('ALTER TABLE invoice ADD amount NUMERIC DEFAULT 0 NOT NULL;');
        $this->addSql('ALTER TABLE invoice ADD payment_fee NUMERIC DEFAULT 0 NOT NULL;');
        $this->addSql('ALTER TABLE invoice ADD owner_team_id INT NOT NULL;');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT invoice_owner_team_id FOREIGN KEY (owner_team_id) REFERENCES team;');
        $this->addSql('ALTER TABLE client_xero_account RENAME TO xero_client_account;');
        $this->addSql('ALTER TABLE client_xero_secret RENAME TO xero_secret;');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE invoice_details RENAME COLUMN quantity TO amount;');
        $this->addSql('ALTER TABLE invoice DROP COLUMN amount;');
        $this->addSql('ALTER TABLE invoice DROP COLUMN payment_fee;');
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT invoice_owner_client_id');
        $this->addSql('ALTER TABLE invoice DROP COLUMN owner_client_id;');
        $this->addSql('ALTER TABLE invoice_details RENAME CONSTRAINT invoice_details_invoice_id TO id;');
        $this->addSql('ALTER TABLE xero_client_account RENAME TO client_xero_account;');
        $this->addSql('ALTER TABLE xero_secret RENAME TO client_xero_secret;');
    }
}
