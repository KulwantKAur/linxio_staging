<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220318090637 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE billing_plan_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE billing_plan (id INT NOT NULL, created_by BIGINT DEFAULT NULL, updated_by BIGINT DEFAULT NULL, name VARCHAR(255) NOT NULL, device_vehicle_active DOUBLE PRECISION DEFAULT NULL, device_vehicle_deactivated DOUBLE PRECISION DEFAULT NULL, device_personal_active DOUBLE PRECISION DEFAULT NULL, device_personal_deactivated DOUBLE PRECISION DEFAULT NULL, device_asset_active DOUBLE PRECISION DEFAULT NULL, device_asset_deactivated DOUBLE PRECISION DEFAULT NULL, vehicle_virtual DOUBLE PRECISION DEFAULT NULL, temp_sensor DOUBLE PRECISION DEFAULT NULL, is_default BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A22865BADE12AB56 ON billing_plan (created_by)');
        $this->addSql('CREATE INDEX IDX_A22865BA16FE72E1 ON billing_plan (updated_by)');
        $this->addSql('ALTER TABLE billing_plan ADD CONSTRAINT FK_A22865BADE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE billing_plan ADD CONSTRAINT FK_A22865BA16FE72E1 FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE client ADD billing_plan_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C744045583B7894C FOREIGN KEY (billing_plan_id) REFERENCES billing_plan (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_C744045583B7894C ON client (billing_plan_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client DROP CONSTRAINT FK_C744045583B7894C');
        $this->addSql('DROP SEQUENCE billing_plan_id_seq CASCADE');
        $this->addSql('DROP TABLE billing_plan');
        $this->addSql('DROP INDEX IDX_C744045583B7894C');
        $this->addSql('ALTER TABLE client DROP billing_plan_id');
    }
}
