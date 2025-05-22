<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201106132725 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE acknowledge_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE acknowledge (id INT NOT NULL, notification_id INT NOT NULL, event_log_id INT NOT NULL, status VARCHAR(50) NOT NULL, comment TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B8037DD4EF1A9D84 ON acknowledge (notification_id)');
        $this->addSql('CREATE INDEX IDX_B8037DD4D8FE2AD4 ON acknowledge (event_log_id)');
        $this->addSql('ALTER TABLE acknowledge ADD CONSTRAINT FK_B8037DD4EF1A9D84 FOREIGN KEY (notification_id) REFERENCES notification (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE acknowledge ADD CONSTRAINT FK_B8037DD4D8FE2AD4 FOREIGN KEY (event_log_id) REFERENCES event_log (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification ADD has_acknowledge BOOLEAN DEFAULT \'false\' NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE acknowledge_id_seq CASCADE');
        $this->addSql('DROP TABLE acknowledge');
        $this->addSql('ALTER TABLE notification DROP has_acknowledge');
    }
}
