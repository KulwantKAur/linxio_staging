<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Resources\procedures\CreatePartitions;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211130093732 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE chat_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE chat_history_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE chat (id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, channel VARCHAR(255) DEFAULT NULL, created_by BIGINT DEFAULT NULL, updated_by BIGINT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, last_sent_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_659DF2AADE12AB56 ON chat (created_by)');
        $this->addSql('CREATE INDEX IDX_659DF2AA16FE72E1 ON chat (updated_by)');
        $this->addSql('CREATE TABLE chat_history (id BIGSERIAL NOT NULL, user_id BIGINT NOT NULL, chat_id INT NOT NULL, created_by BIGINT DEFAULT NULL, updated_by BIGINT DEFAULT NULL, file_id BIGINT DEFAULT NULL, message TEXT  DEFAULT NULL, sent_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, location JSON DEFAULT NULL, is_read BOOLEAN DEFAULT \'false\' NOT NULL) PARTITION BY RANGE (created_at)');
        $this->addSql('CREATE INDEX IDX_6BB4BC22DE12AB56 ON chat_history (created_by)');
        $this->addSql('CREATE INDEX IDX_6BB4BC2216FE72E1 ON chat_history (updated_by)');
        $this->addSql('CREATE INDEX chat_history_user_id_created_at_index ON chat_history (user_id, created_at)');
        $this->addSql('CREATE INDEX chat_history_user_id_sent_at_index ON chat_history (user_id, sent_at)');
        $this->addSql('CREATE INDEX chat_history_created_at_index ON chat_history (created_at)');
        $this->addSql('ALTER TABLE chat ADD CONSTRAINT FK_659DF2AADE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE chat ADD CONSTRAINT FK_659DF2AA16FE72E1 FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE chat_history ADD CONSTRAINT FK_6BB4BC22A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE chat_history ADD CONSTRAINT FK_6BB4BC221A9A7125 FOREIGN KEY (chat_id) REFERENCES chat (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE chat_history ADD CONSTRAINT FK_6BB4BC22DE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE chat_history ADD CONSTRAINT FK_6BB4BC2216FE72E1 FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE chat_history ADD CONSTRAINT FK_6BB4BC22464E68B FOREIGN KEY (file_id) REFERENCES file (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE TABLE chat_users (chat_id INT NOT NULL, user_id BIGINT NOT NULL, PRIMARY KEY(chat_id, user_id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_659DF2AA5E237E06 ON chat (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_659DF2AA8A90ABA9 ON chat (channel)');
        $this->addSql('CREATE INDEX IDX_15FE48721A9A7125 ON chat_users (chat_id)');
        $this->addSql('CREATE INDEX IDX_15FE4872A76ED395 ON chat_users (user_id)');
        $this->addSql('ALTER TABLE chat_users ADD CONSTRAINT FK_15FE48721A9A7125 FOREIGN KEY (chat_id) REFERENCES chat (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE chat_users ADD CONSTRAINT FK_15FE4872A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql(CreatePartitions::up());
        $this->addSql('SELECT create_partitions(\'chat_history\', \'chat_history\', \'1 month\', \'month\', \'YYYY_MM\');');
        $this->addSql('SELECT cron.schedule(\'create_chat_history_partitions\', \'00 12 01 * *\', $$SELECT create_partitions(\'chat_history\', \'chat_history\', \'1 month\', \'month\', \'YYYY_MM\');$$);');

        $this->addSql('ALTER TABLE file ADD remote_path VARCHAR(500) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE file DROP remote_path');

        $this->addSql('SELECT cron.unschedule(\'create_chat_history_partitions\')');

        $this->addSql('DROP SEQUENCE chat_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE chat_history_id_seq CASCADE');
        $this->addSql('DROP TABLE chat_users');
        $this->addSql('DROP TABLE chat_history');
        $this->addSql('DROP TABLE chat');
    }
}
