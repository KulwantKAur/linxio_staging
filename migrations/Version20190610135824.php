<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190610135824 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX uniq_bf5476ca71f7e88b');
        $this->addSql('ALTER TABLE notification ADD listener_team_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notification ADD created_by BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE notification ADD updated_by BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE notification ADD title VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE notification ADD status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE notification ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE notification ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE notification RENAME COLUMN team_id TO owner_team_id');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA51A5E71 FOREIGN KEY (owner_team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAC919A3E1 FOREIGN KEY (listener_team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CADE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA16FE72E1 FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_BF5476CA71F7E88B ON notification (event_id)');
        $this->addSql('CREATE INDEX IDX_BF5476CAA51A5E71 ON notification (owner_team_id)');
        $this->addSql('CREATE INDEX IDX_BF5476CAC919A3E1 ON notification (listener_team_id)');
        $this->addSql('CREATE INDEX IDX_BF5476CADE12AB56 ON notification (created_by)');
        $this->addSql('CREATE INDEX IDX_BF5476CA16FE72E1 ON notification (updated_by)');
        $this->addSql('DROP INDEX uniq_c0731d6dc54c8c93');
        $this->addSql('CREATE INDEX IDX_C0731D6DC54C8C93 ON notification_scopes (type_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C0731D6DEF1A9D84C54C8C93 ON notification_scopes (notification_id, type_id)');
        $this->addSql('DROP INDEX uniq_ca2b6254e16c6b94');
        $this->addSql('ALTER TABLE notification_scope_type ADD sub_type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE notification_scope_type RENAME COLUMN alias TO type');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CA2B62548CDE5729AB48C8E8 ON notification_scope_type (type, sub_type)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX UNIQ_CA2B62548CDE5729AB48C8E8');
        $this->addSql('ALTER TABLE notification_scope_type ADD alias VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE notification_scope_type DROP type');
        $this->addSql('ALTER TABLE notification_scope_type DROP sub_type');
        $this->addSql('CREATE UNIQUE INDEX uniq_ca2b6254e16c6b94 ON notification_scope_type (alias)');
        $this->addSql('DROP INDEX IDX_C0731D6DC54C8C93');
        $this->addSql('DROP INDEX UNIQ_C0731D6DEF1A9D84C54C8C93');
        $this->addSql('CREATE UNIQUE INDEX uniq_c0731d6dc54c8c93 ON notification_scopes (type_id)');
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT FK_BF5476CAA51A5E71');
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT FK_BF5476CAC919A3E1');
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT FK_BF5476CADE12AB56');
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT FK_BF5476CA16FE72E1');
        $this->addSql('DROP INDEX IDX_BF5476CA71F7E88B');
        $this->addSql('DROP INDEX IDX_BF5476CAA51A5E71');
        $this->addSql('DROP INDEX IDX_BF5476CAC919A3E1');
        $this->addSql('DROP INDEX IDX_BF5476CADE12AB56');
        $this->addSql('DROP INDEX IDX_BF5476CA16FE72E1');
        $this->addSql('ALTER TABLE notification ADD team_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notification DROP owner_team_id');
        $this->addSql('ALTER TABLE notification DROP listener_team_id');
        $this->addSql('ALTER TABLE notification DROP created_by');
        $this->addSql('ALTER TABLE notification DROP updated_by');
        $this->addSql('ALTER TABLE notification DROP title');
        $this->addSql('ALTER TABLE notification DROP status');
        $this->addSql('ALTER TABLE notification DROP created_at');
        $this->addSql('ALTER TABLE notification DROP updated_at');
        $this->addSql('CREATE UNIQUE INDEX uniq_bf5476ca71f7e88b ON notification (event_id)');
    }
}
