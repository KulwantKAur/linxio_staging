<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201210121444 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE acknowledge_recipients_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE acknowledge_recipients (id INT NOT NULL, notification_id INT NOT NULL, type VARCHAR(255) NOT NULL, value JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F782A67AEF1A9D84 ON acknowledge_recipients (notification_id)');
        $this->addSql('COMMENT ON COLUMN acknowledge_recipients.value IS \'(DC2Type:json_array)\'');
        $this->addSql('ALTER TABLE acknowledge_recipients ADD CONSTRAINT FK_F782A67AEF1A9D84 FOREIGN KEY (notification_id) REFERENCES notification (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification DROP has_acknowledge');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE acknowledge_recipients_id_seq CASCADE');
        $this->addSql('DROP TABLE acknowledge_recipients');
        $this->addSql('ALTER TABLE notification ADD has_acknowledge BOOLEAN DEFAULT \'false\' NOT NULL');
    }
}
