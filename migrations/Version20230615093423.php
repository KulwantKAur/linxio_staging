<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230615093423 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE client ADD chevron_account_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE reseller ADD chevron_account_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE INDEX client_chevron_account_id_index ON client (chevron_account_id)');
        $this->addSql('CREATE INDEX reseller_chevron_account_id_index ON reseller (chevron_account_id)');

    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP INDEX reseller_chevron_account_id_index');
        $this->addSql('DROP INDEX client_chevron_account_id_index');
        $this->addSql('ALTER TABLE reseller DROP chevron_account_id');
        $this->addSql('ALTER TABLE client DROP chevron_account_id');
    }
}
