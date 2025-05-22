<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200901124936 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE track_link_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE track_link (id INT NOT NULL, vehicle_id INT DEFAULT NULL, created_by BIGINT DEFAULT NULL, driver_name VARCHAR(255) DEFAULT NULL, message TEXT DEFAULT NULL, hash VARCHAR(255) NOT NULL, date_from TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, date_to TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_ABFAB1F4545317D1 ON track_link (vehicle_id)');
        $this->addSql('CREATE INDEX IDX_ABFAB1F4DE12AB56 ON track_link (created_by)');
        $this->addSql('ALTER TABLE track_link ADD CONSTRAINT FK_ABFAB1F4545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE track_link ADD CONSTRAINT FK_ABFAB1F4DE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE track_link_id_seq CASCADE');
        $this->addSql('DROP TABLE track_link');
    }
}
