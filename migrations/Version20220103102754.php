<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220103102754 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD last_online_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('CREATE INDEX users_network_status_last_online_date_index ON users (network_status, last_online_date)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX users_network_status_last_online_date_index');
        $this->addSql('ALTER TABLE users DROP last_online_date');
    }
}
