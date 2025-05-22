<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210408115128 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE scheduled_report_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE scheduled_report_recipients_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE scheduled_report (id INT NOT NULL, team_id INT DEFAULT NULL, recipient_id INT DEFAULT NULL, created_by BIGINT DEFAULT NULL, updated_by BIGINT DEFAULT NULL, type VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, interval JSON NOT NULL, format VARCHAR(50) NOT NULL, params JSONB DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_68D1F39D296CD8AE ON scheduled_report (team_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_68D1F39DE92F8F78 ON scheduled_report (recipient_id)');
        $this->addSql('CREATE INDEX IDX_68D1F39DDE12AB56 ON scheduled_report (created_by)');
        $this->addSql('CREATE INDEX IDX_68D1F39D16FE72E1 ON scheduled_report (updated_by)');
        $this->addSql('COMMENT ON COLUMN scheduled_report.interval IS \'(DC2Type:json_array)\'');
        $this->addSql('CREATE TABLE scheduled_report_recipients (id INT NOT NULL, type VARCHAR(50) DEFAULT NULL, value JSON DEFAULT NULL, custom JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN scheduled_report_recipients.value IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN scheduled_report_recipients.custom IS \'(DC2Type:json_array)\'');
        $this->addSql('ALTER TABLE scheduled_report ADD CONSTRAINT FK_68D1F39D296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE scheduled_report ADD CONSTRAINT FK_68D1F39DE92F8F78 FOREIGN KEY (recipient_id) REFERENCES scheduled_report_recipients (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE scheduled_report ADD CONSTRAINT FK_68D1F39DDE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE scheduled_report ADD CONSTRAINT FK_68D1F39D16FE72E1 FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE scheduled_report ADD sent_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE scheduled_report DROP CONSTRAINT FK_68D1F39DE92F8F78');
        $this->addSql('DROP SEQUENCE scheduled_report_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE scheduled_report_recipients_id_seq CASCADE');
        $this->addSql('DROP TABLE scheduled_report');
        $this->addSql('DROP TABLE scheduled_report_recipients');
    }
}
