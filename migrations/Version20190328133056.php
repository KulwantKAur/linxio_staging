<?php

namespace Application\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190328133056 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE sms_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE otp_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE sms (id INT NOT NULL, user_id BIGINT DEFAULT NULL, phone_from VARCHAR(20) NOT NULL, phone_to VARCHAR(20) NOT NULL, status VARCHAR(255) NOT NULL, message TEXT DEFAULT NULL, message_uuid VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B0A93A7778EACB7F ON sms (message_uuid)');
        $this->addSql('CREATE INDEX IDX_B0A93A77A76ED395 ON sms (user_id)');
        $this->addSql('CREATE TABLE otp (id INT NOT NULL, user_id BIGINT DEFAULT NULL, phone VARCHAR(20) NOT NULL, code VARCHAR(20) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, verified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A79C98C1A76ED395 ON otp (user_id)');
        $this->addSql('ALTER TABLE sms ADD CONSTRAINT FK_B0A93A77A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE otp ADD CONSTRAINT FK_A79C98C1A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE sms_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE otp_id_seq CASCADE');
        $this->addSql('DROP TABLE sms');
        $this->addSql('DROP TABLE otp');
    }
}
