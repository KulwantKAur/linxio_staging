<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210203133531 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE vehicle_engine_hours_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE vehicle_engine_hours (id INT NOT NULL, vehicle_id INT DEFAULT NULL, created_by BIGINT NOT NULL, engine_hours INT NOT NULL, prev_engine_hours INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1E9A86B2545317D1 ON vehicle_engine_hours (vehicle_id)');
        $this->addSql('CREATE INDEX IDX_1E9A86B2DE12AB56 ON vehicle_engine_hours (created_by)');
        $this->addSql('ALTER TABLE vehicle_engine_hours ADD CONSTRAINT FK_1E9A86B2545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vehicle_engine_hours ADD CONSTRAINT FK_1E9A86B2DE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE vehicle_engine_hours_id_seq CASCADE');
        $this->addSql('DROP TABLE vehicle_engine_hours');
    }
}
