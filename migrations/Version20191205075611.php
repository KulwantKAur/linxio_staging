<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191205075611 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE route ADD start_odometer DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE route ADD finish_odometer DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE route ADD start_coordinates geometry(POINT, 0) DEFAULT NULL');
        $this->addSql('ALTER TABLE route ADD finish_coordinates geometry(POINT, 0) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE route DROP start_odometer');
        $this->addSql('ALTER TABLE route DROP finish_odometer');
        $this->addSql('ALTER TABLE route DROP start_coordinates');
        $this->addSql('ALTER TABLE route DROP finish_coordinates');
    }
}