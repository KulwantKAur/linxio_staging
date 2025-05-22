<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220420151329 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX thtp_device_id_speed_ts_index ON tracker_history_temp_part (device_id, speed, ts)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX thtp_device_id_speed_ts_index');
    }
}
