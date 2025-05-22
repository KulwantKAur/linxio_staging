<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211221144148 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX chat_channel_created_at_index ON chat (channel, created_at)');
        $this->addSql('DROP INDEX uniq_659df2aa5e237e06');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX uniq_659df2aa5e237e06 ON chat (name)');
        $this->addSql('DROP INDEX chat_channel_created_at_index');
    }
}
