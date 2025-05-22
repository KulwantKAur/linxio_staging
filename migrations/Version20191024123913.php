<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191024123913 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('UPDATE fuel_type SET name=\'Gas\' WHERE  id = 3');
        $this->addSql('UPDATE fuel_type SET name=\'Electric engine\' WHERE  id = 4');
        $this->removeUnusedFuelType();
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('UPDATE fuel_type SET name=\'LPG\' WHERE  id = 3');
        $this->addSql('UPDATE fuel_type SET name=\'Biofuels\' WHERE  id = 4');
        $this->insertUnusedFuelType();
    }

    private function removeUnusedFuelType()
    {
        $this->addSql('DELETE FROM fuel_type WHERE id IN (5, 6)');
    }

    private function insertUnusedFuelType()
    {
        $this->addSql('INSERT INTO fuel_type (id, name) VALUES (5, \'Biofuels\')');
        $this->addSql('INSERT INTO fuel_type (id, name) VALUES (6, \'Hybrid\')');
    }
}
