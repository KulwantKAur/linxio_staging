<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211129120227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idling_vehicle_id_started_at_finished_at_index');
        $this->addSql('CREATE INDEX idling_vehicle_id_duration_started_at_finished_at_index ON idling (vehicle_id, duration, started_at, finished_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idling_vehicle_id_duration_started_at_finished_at_index');
        $this->addSql('CREATE INDEX idling_vehicle_id_started_at_finished_at_index ON idling (vehicle_id, started_at, finished_at)');
    }
}
