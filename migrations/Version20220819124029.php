<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220819124029 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE xero_client_account RENAME CONSTRAINT fk_d420da7e19eb6921 TO xero_client_account_client_id;');
        $this->addSql('ALTER TABLE xero_secret ADD xero_account_code VARCHAR(255) DEFAULT NULL');
        $this->addSql('DROP SEQUENCE client_xero_secret_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE client_xero_account_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE xero_client_account_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE xero_secret_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE xero_secret DROP COLUMN xero_account_code;');
        $this->addSql('ALTER TABLE xero_client_account RENAME CONSTRAINT xero_client_account_client_id TO fk_d420da7e19eb6921;');
        $this->addSql('DROP SEQUENCE xero_client_account_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE xero_secret_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE client_xero_secret_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE client_xero_account_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
    }
}
