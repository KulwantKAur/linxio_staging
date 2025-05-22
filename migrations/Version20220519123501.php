<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220519123501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE device_installation ADD is_odometer_synced BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('UPDATE device_installation SET is_odometer_synced = true');
        $this->addSql('CREATE INDEX device_installation_is_odometer_synced_index ON device_installation (is_odometer_synced)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX device_installation_is_odometer_synced_index');
        $this->addSql('ALTER TABLE device_installation DROP is_odometer_synced');
    }
}
