<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210505085429 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE device_sensor DROP CONSTRAINT FK_6F9666ABA247991F');
        $this->addSql('ALTER TABLE device_sensor ADD status SMALLINT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE device_sensor ADD last_occurred_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE device_sensor ADD CONSTRAINT FK_6F9666ABA247991F FOREIGN KEY (sensor_id) REFERENCES sensor (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE device_sensor DROP CONSTRAINT fk_6f9666aba247991f');
        $this->addSql('ALTER TABLE device_sensor DROP status');
        $this->addSql('ALTER TABLE device_sensor DROP last_occurred_at');
        $this->addSql('ALTER TABLE device_sensor ADD CONSTRAINT fk_6f9666aba247991f FOREIGN KEY (sensor_id) REFERENCES sensor (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
