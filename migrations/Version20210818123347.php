<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210818123347 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tracker_history_last DROP CONSTRAINT FK_A785295DDFB8E61F');
        $this->addSql('ALTER TABLE tracker_history_last ALTER tracker_history_id TYPE INT');
        $this->addSql('ALTER TABLE tracker_history_last ALTER tracker_history_id DROP DEFAULT');
        $this->addSql('ALTER TABLE tracker_history_last ADD CONSTRAINT FK_A785295DDFB8E61F FOREIGN KEY (tracker_history_id) REFERENCES tracker_history (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tracker_history_last DROP CONSTRAINT fk_a785295ddfb8e61f');
        $this->addSql('ALTER TABLE tracker_history_last ALTER tracker_history_id TYPE BIGINT');
        $this->addSql('ALTER TABLE tracker_history_last ALTER tracker_history_id DROP DEFAULT');
        $this->addSql('ALTER TABLE tracker_history_last ADD CONSTRAINT fk_a785295ddfb8e61f FOREIGN KEY (tracker_history_id) REFERENCES tracker_history (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
