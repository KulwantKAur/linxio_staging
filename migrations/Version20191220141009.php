<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191220141009 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE driving_behavior DROP CONSTRAINT FK_E08B3788DFB8E61F');
        $this->addSql('ALTER TABLE driving_behavior DROP CONSTRAINT FK_E08B3788545317D1');
        $this->addSql('ALTER TABLE driving_behavior DROP ignition');
        $this->addSql('ALTER TABLE driving_behavior ADD CONSTRAINT FK_E08B3788DFB8E61F FOREIGN KEY (tracker_history_id) REFERENCES tracker_history (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE driving_behavior ADD CONSTRAINT FK_E08B3788545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE driving_behavior DROP CONSTRAINT fk_e08b3788dfb8e61f');
        $this->addSql('ALTER TABLE driving_behavior DROP CONSTRAINT fk_e08b3788545317d1');
        $this->addSql('ALTER TABLE driving_behavior ADD ignition SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE driving_behavior ADD CONSTRAINT fk_e08b3788dfb8e61f FOREIGN KEY (tracker_history_id) REFERENCES tracker_history (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE driving_behavior ADD CONSTRAINT fk_e08b3788545317d1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
