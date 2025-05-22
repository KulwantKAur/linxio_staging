<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220714075411 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX device_traccar_device_id_index ON device (traccar_device_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX device_traccar_device_id_index');
    }
}
