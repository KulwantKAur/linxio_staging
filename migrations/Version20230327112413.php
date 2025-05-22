<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230327112413 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE SEQUENCE device_replacement_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE device_replacement (id BIGINT NOT NULL, device_old_id INT DEFAULT NULL, device_new_id INT DEFAULT NULL, created_by BIGINT DEFAULT NULL, updated_by BIGINT DEFAULT NULL, team_id INT NOT NULL, imei_old VARCHAR(50) NOT NULL, imei_new VARCHAR(50) NOT NULL, reason TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2655A08CEAF68B0D ON device_replacement (device_old_id)');
        $this->addSql('CREATE INDEX IDX_2655A08C6E16C2A8 ON device_replacement (device_new_id)');
        $this->addSql('CREATE INDEX IDX_2655A08CDE12AB56 ON device_replacement (created_by)');
        $this->addSql('CREATE INDEX IDX_2655A08C16FE72E1 ON device_replacement (updated_by)');
        $this->addSql('CREATE INDEX IDX_2655A08C296CD8AE ON device_replacement (team_id)');
        $this->addSql('ALTER TABLE device_replacement ADD CONSTRAINT FK_2655A08CEAF68B0D FOREIGN KEY (device_old_id) REFERENCES device (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE device_replacement ADD CONSTRAINT FK_2655A08C6E16C2A8 FOREIGN KEY (device_new_id) REFERENCES device (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE device_replacement ADD CONSTRAINT FK_2655A08CDE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE device_replacement ADD CONSTRAINT FK_2655A08C16FE72E1 FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE device_replacement ADD CONSTRAINT FK_2655A08C296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP SEQUENCE device_replacement_id_seq CASCADE');
        $this->addSql('DROP TABLE device_replacement');
    }
}
