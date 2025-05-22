<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220811155253 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE client_xero_secret ADD xero_tenant_id VARCHAR;');
        $this->addSql('ALTER TABLE client_xero_account RENAME COLUMN xero_tenant_id TO xero_contact_id;');
        $this->addSql('ALTER TABLE invoice ADD xero_invoice_id VARCHAR;');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE client_xero_secret DROP COLUMN xero_tenant_id;');
        $this->addSql('ALTER TABLE client_xero_account RENAME COLUMN xero_contact_id TO xero_tenant_id;');
        $this->addSql('ALTER TABLE invoice DROP COLUMN xero_invoice_id;');
    }
}
