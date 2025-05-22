<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231214135014 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tracker_payload_temp (id BIGSERIAL NOT NULL, tracker_auth_id BIGINT DEFAULT NULL, device_id INT DEFAULT NULL, payload TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, is_processed BOOLEAN DEFAULT \'false\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BF9E98C6D7DDFC70 ON tracker_payload_temp (tracker_auth_id)');
        $this->addSql('CREATE INDEX IDX_BF9E98C694A4C7D4 ON tracker_payload_temp (device_id)');
        $this->addSql('CREATE INDEX tracker_payload_temp_device_id_created_at_index ON tracker_payload_temp (device_id, created_at)');
        $this->addSql('ALTER TABLE tracker_payload_temp ADD CONSTRAINT FK_BF9E98C6D7DDFC70 FOREIGN KEY (tracker_auth_id) REFERENCES tracker_auth (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_payload_temp ADD CONSTRAINT FK_BF9E98C694A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE tracker_payload_temp');

    }
}
