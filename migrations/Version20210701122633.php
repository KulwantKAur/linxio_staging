<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210701122633 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE asset_sensor_history_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE asset_sensor_history (id INT NOT NULL, asset_id INT NOT NULL, sensor_id INT NOT NULL, installed_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, uninstalled_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D5D3165DA1941 ON asset_sensor_history (asset_id)');
        $this->addSql('CREATE INDEX IDX_D5D316A247991F ON asset_sensor_history (sensor_id)');
        $this->addSql('CREATE INDEX asset_sensor_history_asset_id_sensor_id_installed_at_index ON asset_sensor_history (asset_id, sensor_id, installed_at)');
        $this->addSql('CREATE INDEX asset_sensor_history_asset_id_sensor_id_uninstalled_at_index ON asset_sensor_history (asset_id, sensor_id, uninstalled_at)');
        $this->addSql('ALTER TABLE asset_sensor_history ADD CONSTRAINT FK_D5D3165DA1941 FOREIGN KEY (asset_id) REFERENCES asset (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE asset_sensor_history ADD CONSTRAINT FK_D5D316A247991F FOREIGN KEY (sensor_id) REFERENCES sensor (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE asset_sensor_history_id_seq CASCADE');
        $this->addSql('DROP TABLE asset_sensor_history');
    }
}
