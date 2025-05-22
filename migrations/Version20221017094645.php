<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221017094645 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fuel_card ADD COLUMN team_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fuel_card ADD COLUMN odometer BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE fuel_card ADD COLUMN card_account_number BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE fuel_card ADD COLUMN site_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE fuel_card ADD COLUMN product_code BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE fuel_card ADD COLUMN pump_price DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('CREATE INDEX fuel_card_team_id_index ON fuel_card (team_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE fuel_card DROP COLUMN fuel_card');
        $this->addSql('ALTER TABLE fuel_card DROP COLUMN odometer');
        $this->addSql('ALTER TABLE fuel_card DROP COLUMN card_account_number');
        $this->addSql('ALTER TABLE fuel_card DROP COLUMN site_id');
        $this->addSql('ALTER TABLE fuel_card DROP COLUMN product_code');
        $this->addSql('ALTER TABLE fuel_card DROP COLUMN pump_price');
        $this->addSql('DROP INDEX fuel_card_team_id_index');
    }
}
