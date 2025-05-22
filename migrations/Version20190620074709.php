<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190620074709 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE service_record_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE reminder_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE service_record (id INT NOT NULL, reminder_id INT DEFAULT NULL, created_by BIGINT DEFAULT NULL, updated_by BIGINT DEFAULT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, note TEXT DEFAULT NULL, cost DOUBLE PRECISION DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A5F39AA7D987BE75 ON service_record (reminder_id)');
        $this->addSql('CREATE INDEX IDX_A5F39AA7DE12AB56 ON service_record (created_by)');
        $this->addSql('CREATE INDEX IDX_A5F39AA716FE72E1 ON service_record (updated_by)');
        $this->addSql('CREATE TABLE service_record_file (service_record_id INT NOT NULL, file_id INT NOT NULL, PRIMARY KEY(service_record_id, file_id))');
        $this->addSql('CREATE INDEX IDX_A5B28168156C4F46 ON service_record_file (service_record_id)');
        $this->addSql('CREATE INDEX IDX_A5B2816893CB796C ON service_record_file (file_id)');
        $this->addSql('CREATE TABLE reminder (id INT NOT NULL, vehicle_id INT DEFAULT NULL, created_by BIGINT DEFAULT NULL, updated_by BIGINT DEFAULT NULL, title VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, date_period INT DEFAULT NULL, date_notification INT DEFAULT NULL, mileage INT DEFAULT NULL, mileage_period INT DEFAULT NULL, mileage_notification INT DEFAULT NULL, hours INT DEFAULT NULL, hours_period INT DEFAULT NULL, hours_notification INT DEFAULT NULL, note TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_40374F40545317D1 ON reminder (vehicle_id)');
        $this->addSql('CREATE INDEX IDX_40374F40DE12AB56 ON reminder (created_by)');
        $this->addSql('CREATE INDEX IDX_40374F4016FE72E1 ON reminder (updated_by)');
        $this->addSql('ALTER TABLE service_record ADD CONSTRAINT FK_A5F39AA7D987BE75 FOREIGN KEY (reminder_id) REFERENCES reminder (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE service_record ADD CONSTRAINT FK_A5F39AA7DE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE service_record ADD CONSTRAINT FK_A5F39AA716FE72E1 FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE service_record_file ADD CONSTRAINT FK_A5B28168156C4F46 FOREIGN KEY (service_record_id) REFERENCES service_record (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE service_record_file ADD CONSTRAINT FK_A5B2816893CB796C FOREIGN KEY (file_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reminder ADD CONSTRAINT FK_40374F40545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reminder ADD CONSTRAINT FK_40374F40DE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reminder ADD CONSTRAINT FK_40374F4016FE72E1 FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE service_record_file DROP CONSTRAINT FK_A5B28168156C4F46');
        $this->addSql('ALTER TABLE service_record DROP CONSTRAINT FK_A5F39AA7D987BE75');
        $this->addSql('DROP SEQUENCE service_record_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE reminder_id_seq CASCADE');
        $this->addSql('DROP TABLE service_record');
        $this->addSql('DROP TABLE service_record_file');
        $this->addSql('DROP TABLE reminder');
    }
}
