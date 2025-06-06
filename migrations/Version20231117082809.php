<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231117082809 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vehicle_engine_hours ALTER engine_hours TYPE BIGINT');
        $this->addSql('ALTER TABLE vehicle_engine_hours ALTER engine_hours DROP DEFAULT');
        $this->addSql('ALTER TABLE vehicle_engine_hours ALTER prev_engine_hours TYPE BIGINT');
        $this->addSql('ALTER TABLE vehicle_engine_hours ALTER prev_engine_hours DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vehicle_engine_hours ALTER engine_hours TYPE INT');
        $this->addSql('ALTER TABLE vehicle_engine_hours ALTER engine_hours DROP DEFAULT');
        $this->addSql('ALTER TABLE vehicle_engine_hours ALTER prev_engine_hours TYPE INT');
        $this->addSql('ALTER TABLE vehicle_engine_hours ALTER prev_engine_hours DROP DEFAULT');
    }
}
