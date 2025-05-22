<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221102143402 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE client ALTER is_manual_payment SET DEFAULT \'true\'');
        $this->addSql('ALTER TABLE invoice ADD payment_status VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE client ALTER is_manual_payment SET DEFAULT \'false\'');
        $this->addSql('ALTER TABLE invoice DROP payment_status');
    }
}
