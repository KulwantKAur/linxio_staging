<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240918105939 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX notification_message_duplicate_index ON notification_message (notification_id, occurrence_time, transport_type, recipient)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX notification_message_duplicate_index');
    }
}
