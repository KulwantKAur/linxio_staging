<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200803085009 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE tracker_topflytech_payload_id_seq CASCADE');
        $this->addSql('DROP TABLE tracker_topflytech_payload');

        $this->addSql('ALTER TABLE tracker_sensor DROP CONSTRAINT FK_8B4EDEC410DB296A');
        $this->addSql('ALTER TABLE tracker_sensor DROP CONSTRAINT FK_8B4EDEC4DFB8E61F');
        $this->addSql('ALTER TABLE tracker_sensor ADD CONSTRAINT FK_8B4EDEC410DB296A FOREIGN KEY (tracker_payload_id) REFERENCES tracker_payload (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_sensor ADD CONSTRAINT FK_8B4EDEC4DFB8E61F FOREIGN KEY (tracker_history_id) REFERENCES tracker_history (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE tracker_payload DROP CONSTRAINT FK_157317F9D7DDFC70');
        $this->addSql('ALTER TABLE tracker_payload ADD CONSTRAINT FK_157317F9D7DDFC70 FOREIGN KEY (tracker_auth_id) REFERENCES tracker_auth (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_payload ADD device_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tracker_payload ADD CONSTRAINT FK_157317F994A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_157317F994A4C7D4 ON tracker_payload (device_id)');

        $this->updatePayloadDevice();

        $this->addSql('ALTER TABLE tracker_history_last DROP CONSTRAINT fk_a785295d10db296a');
        $this->addSql('DROP INDEX idx_a785295d10db296a');
        $this->addSql('ALTER TABLE tracker_history_last DROP tracker_payload_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE tracker_history_last ADD tracker_payload_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tracker_history_last ADD CONSTRAINT fk_a785295d10db296a FOREIGN KEY (tracker_payload_id) REFERENCES tracker_payload (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_a785295d10db296a ON tracker_history_last (tracker_payload_id)');

        $this->addSql('ALTER TABLE tracker_payload DROP CONSTRAINT FK_157317F994A4C7D4');
        $this->addSql('DROP INDEX IDX_157317F994A4C7D4');
        $this->addSql('ALTER TABLE tracker_payload DROP device_id');
        $this->addSql('ALTER TABLE tracker_payload DROP CONSTRAINT fk_157317f9d7ddfc70');
        $this->addSql('ALTER TABLE tracker_payload ADD CONSTRAINT fk_157317f9d7ddfc70 FOREIGN KEY (tracker_auth_id) REFERENCES tracker_auth (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE tracker_sensor DROP CONSTRAINT fk_8b4edec410db296a');
        $this->addSql('ALTER TABLE tracker_sensor DROP CONSTRAINT fk_8b4edec4dfb8e61f');
        $this->addSql('ALTER TABLE tracker_sensor ADD CONSTRAINT fk_8b4edec410db296a FOREIGN KEY (tracker_payload_id) REFERENCES tracker_payload (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_sensor ADD CONSTRAINT fk_8b4edec4dfb8e61f FOREIGN KEY (tracker_history_id) REFERENCES tracker_history (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE SEQUENCE tracker_topflytech_payload_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE tracker_topflytech_payload (id INT NOT NULL, payload TEXT NOT NULL, socket_id VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
    }

    private function updatePayloadDevice()
    {
        $this->addSql('UPDATE tracker_payload
            SET device_id = devices.d_id
            FROM (SELECT tp.id AS tp_id, ta.device_id AS d_id
                  FROM tracker_payload tp
                           LEFT JOIN tracker_auth ta ON tp.tracker_auth_id = ta.id
                  WHERE tp.device_id IS NULL) AS devices
            WHERE id = devices.tp_id
             AND device_id IS NULL
            ;
        ');

    }

}
