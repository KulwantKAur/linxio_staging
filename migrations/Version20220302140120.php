<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220302140120 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE device DROP last_data_received_at');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE device ADD last_data_received_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }
}
