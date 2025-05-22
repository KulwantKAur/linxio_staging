<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190809104852 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE tracker_history ADD movement INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tracker_history ADD ignition INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tracker_history ADD battery_voltage DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE tracker_history ADD temperature_level DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE tracker_history ADD engine_hours DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE tracker_history ADD gsm_signal INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tracker_history ADD odometer DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER INDEX idx_2c4207994a4c7d4 RENAME TO IDX_C441C01E94A4C7D4');
        $this->addSql('ALTER INDEX idx_2c42079f058a3f9 RENAME TO IDX_C441C01EF058A3F9');
        $this->addSql('ALTER INDEX idx_2c42079886802c5 RENAME TO IDX_C441C01E886802C5');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER INDEX idx_c441c01e94a4c7d4 RENAME TO idx_2c4207994a4c7d4');
        $this->addSql('ALTER INDEX idx_c441c01ef058a3f9 RENAME TO idx_2c42079f058a3f9');
        $this->addSql('ALTER INDEX idx_c441c01e886802c5 RENAME TO idx_2c42079886802c5');
        $this->addSql('ALTER TABLE tracker_history DROP movement');
        $this->addSql('ALTER TABLE tracker_history DROP ignition');
        $this->addSql('ALTER TABLE tracker_history DROP battery_voltage');
        $this->addSql('ALTER TABLE tracker_history DROP temperature_level');
        $this->addSql('ALTER TABLE tracker_history DROP engine_hours');
        $this->addSql('ALTER TABLE tracker_history DROP gsm_signal');
        $this->addSql('ALTER TABLE tracker_history DROP odometer');
    }
}
