<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220415090325 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE billing_plan ADD vehicle_archived DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE billing_plan ADD sensor_archived DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE billing_plan DROP vehicle_archived');
        $this->addSql('ALTER TABLE billing_plan DROP sensor_archived');
    }
}
