<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211230074506 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chat ADD last_chat_history_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE chat DROP last_sent_at');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_659DF2AA334F81C4 ON chat (last_chat_history_id)');
        $this->addSql('ALTER TABLE chat_history DROP CONSTRAINT fk_6bb4bc22de12ab56');
        $this->addSql('ALTER TABLE chat_history DROP CONSTRAINT fk_6bb4bc2216fe72e1');
        $this->addSql('DROP INDEX chat_history_user_id_sent_at_index');
        $this->addSql('DROP INDEX idx_6bb4bc22de12ab56');
        $this->addSql('DROP INDEX idx_6bb4bc2216fe72e1');
        $this->addSql('ALTER TABLE chat_history DROP created_by');
        $this->addSql('ALTER TABLE chat_history DROP updated_by');
        $this->addSql('ALTER TABLE chat_history DROP sent_at');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chat_history ADD created_by BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE chat_history ADD updated_by BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE chat_history ADD sent_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE chat_history ADD CONSTRAINT fk_6bb4bc22de12ab56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE chat_history ADD CONSTRAINT fk_6bb4bc2216fe72e1 FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX chat_history_user_id_sent_at_index ON chat_history (user_id, sent_at)');
        $this->addSql('CREATE INDEX idx_6bb4bc22de12ab56 ON chat_history (created_by)');
        $this->addSql('CREATE INDEX idx_6bb4bc2216fe72e1 ON chat_history (updated_by)');
        $this->addSql('DROP INDEX UNIQ_659DF2AA334F81C4');
        $this->addSql('ALTER TABLE chat ADD last_sent_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE chat DROP last_chat_history_id');
    }
}
