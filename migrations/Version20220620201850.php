<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220620201850 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client DROP CONSTRAINT fk_c744045583b7894c');
        $this->addSql('DROP INDEX uniq_c7440455296cd8ae');
        $this->addSql('DROP INDEX idx_c744045583b7894c');
        $this->addSql('ALTER TABLE client DROP billing_plan_id');
        $this->addSql('CREATE INDEX IDX_C7440455296CD8AE ON client (team_id)');
        $this->addSql('ALTER TABLE file ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE file ALTER id DROP DEFAULT');
        $this->addSql('ALTER INDEX idx_837dc4ac71f7e88b RENAME TO IDX_5795D59371F7E88B');
        $this->addSql('ALTER INDEX idx_837dc4ac5b1f2b51 RENAME TO IDX_5795D5935B1F2B51');
        $this->addSql('ALTER INDEX idx_837dc4acf214c8c RENAME TO IDX_5795D593F214C8C');
        $this->addSql('ALTER TABLE notification_alert_subtype ALTER sort SET DEFAULT 10');
        $this->addSql('DROP INDEX uniq_a22865ba296cd8ae');
        $this->addSql('CREATE INDEX IDX_A22865BA296CD8AE ON billing_plan (team_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_C7440455296CD8AE');
        $this->addSql('ALTER TABLE client ADD billing_plan_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT fk_c744045583b7894c FOREIGN KEY (billing_plan_id) REFERENCES billing_plan (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_c7440455296cd8ae ON client (team_id)');
        $this->addSql('CREATE INDEX idx_c744045583b7894c ON client (billing_plan_id)');
        $this->addSql('ALTER TABLE file ALTER id TYPE INT');
        $this->addSql('ALTER TABLE file ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE notification_alert_subtype ALTER sort SET DEFAULT 0');
        $this->addSql('ALTER INDEX idx_5795d5935b1f2b51 RENAME TO idx_837dc4ac5b1f2b51');
        $this->addSql('ALTER INDEX idx_5795d593f214c8c RENAME TO idx_837dc4acf214c8c');
        $this->addSql('ALTER INDEX idx_5795d59371f7e88b RENAME TO idx_837dc4ac71f7e88b');
        $this->addSql('DROP INDEX IDX_A22865BA296CD8AE');
        $this->addSql('CREATE UNIQUE INDEX uniq_a22865ba296cd8ae ON billing_plan (team_id)');
    }
}
