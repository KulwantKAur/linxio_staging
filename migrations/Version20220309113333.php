<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220309113333 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chat_history ADD type SMALLINT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE chat_history ADD event SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE chat_history ADD event_source BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE chat_history ALTER user_id DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chat_history ALTER user_id SET NOT NULL');
        $this->addSql('ALTER TABLE chat_history DROP event_source');
        $this->addSql('ALTER TABLE chat_history DROP event');
        $this->addSql('ALTER TABLE chat_history DROP type');
    }
}
