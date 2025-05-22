<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211224120545 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chat_history DROP is_read');
        $this->addSql('CREATE SEQUENCE chat_history_unread_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE chat_history_unread (id BIGINT NOT NULL, user_id BIGINT NOT NULL, chat_id INT NOT NULL, chat_history_id BIGINT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B045A868A76ED395 ON chat_history_unread (user_id)');
        $this->addSql('CREATE INDEX IDX_B045A8681A9A7125 ON chat_history_unread (chat_id)');
        $this->addSql('CREATE INDEX IDX_B045A868D9F4C1F4 ON chat_history_unread (chat_history_id)');
        $this->addSql('CREATE INDEX chat_history_unread_chat_id_user_id_index ON chat_history_unread (chat_id, user_id)');
        $this->addSql('ALTER TABLE chat_history_unread ADD CONSTRAINT FK_B045A868A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE chat_history_unread ADD CONSTRAINT FK_B045A8681A9A7125 FOREIGN KEY (chat_id) REFERENCES chat (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users ADD network_status SMALLINT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP network_status');
        $this->addSql('DROP SEQUENCE chat_history_unread_id_seq CASCADE');
        $this->addSql('DROP TABLE chat_history_unread');
        $this->addSql('ALTER TABLE chat_history ADD is_read BOOLEAN DEFAULT \'false\' NOT NULL');
    }
}
