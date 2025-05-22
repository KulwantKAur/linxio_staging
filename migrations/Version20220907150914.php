<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220907150914 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE invoice ADD payment_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE invoice ADD ext_payment_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE invoice ADD ext_paid BOOLEAN DEFAULT \'false\' NOT NULL');
        $this->addSql('ALTER TABLE invoice RENAME COLUMN xero_invoice_id TO ext_invoice_id');
        $this->addSql('ALTER TABLE invoice ADD total_amount NUMERIC DEFAULT 0 NOT NULL;');
        $this->addSql('ALTER TABLE invoice ADD tax NUMERIC DEFAULT 0 NOT NULL;');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE invoice RENAME COLUMN ext_invoice_id TO xero_invoice_id');
        $this->addSql('ALTER TABLE invoice DROP payment_id');
        $this->addSql('ALTER TABLE invoice DROP ext_payment_id');
        $this->addSql('ALTER TABLE invoice DROP ext_paid');
        $this->addSql('ALTER TABLE invoice DROP total_amount');
        $this->addSql('ALTER TABLE invoice DROP tax');
    }
}
