<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200108141616 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE repair_data_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE repair_data (id INT NOT NULL, reminder_category INT DEFAULT NULL, service_record_id INT NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_78D52783643F1E57 ON repair_data (reminder_category)');
        $this->addSql('CREATE INDEX IDX_78D52783156C4F46 ON repair_data (service_record_id)');
        $this->addSql('ALTER TABLE repair_data ADD CONSTRAINT FK_78D52783643F1E57 FOREIGN KEY (reminder_category) REFERENCES reminder_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE repair_data ADD CONSTRAINT FK_78D52783156C4F46 FOREIGN KEY (service_record_id) REFERENCES service_record (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE service_record ADD repair_data INT DEFAULT NULL');
        $this->addSql('ALTER TABLE service_record ADD CONSTRAINT FK_A5F39AA778D52783 FOREIGN KEY (repair_data) REFERENCES repair_data (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_A5F39AA778D52783 ON service_record (repair_data)');
        $this->addSql('DROP INDEX route_type_started_at_finished_at_index');
        $this->addSql('DROP INDEX idling_started_at_finished_at_index');
        $this->addSql('ALTER INDEX idx_837dc4ac71f7e88b RENAME TO IDX_5795D59371F7E88B');
        $this->addSql('ALTER INDEX idx_837dc4ac5b1f2b51 RENAME TO IDX_5795D5935B1F2B51');
        $this->addSql('ALTER INDEX idx_837dc4acf214c8c RENAME TO IDX_5795D593F214C8C');
        $this->addSql('ALTER TABLE repair_data ADD vehicle_id INT NOT NULL');
        $this->addSql('ALTER TABLE repair_data ADD CONSTRAINT FK_78D52783545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_78D52783545317D1 ON repair_data (vehicle_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE service_record DROP CONSTRAINT FK_A5F39AA778D52783');
        $this->addSql('DROP SEQUENCE repair_data_id_seq CASCADE');
        $this->addSql('DROP TABLE repair_data');
        $this->addSql('DROP INDEX IDX_A5F39AA778D52783');
        $this->addSql('ALTER TABLE service_record DROP repair_data');
        $this->addSql('ALTER TABLE repair_data DROP CONSTRAINT FK_78D52783545317D1');
        $this->addSql('DROP INDEX IDX_78D52783545317D1');
        $this->addSql('ALTER TABLE repair_data DROP vehicle_id');
    }
}
