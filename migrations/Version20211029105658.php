<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211029105658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tracker_history_io_last (id SERIAL NOT NULL, device_id INT NOT NULL, tracker_history_io_id INT NOT NULL, type_id INT NOT NULL, occurred_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A17041B794A4C7D4 ON tracker_history_io_last (device_id)');
        $this->addSql('CREATE INDEX IDX_A17041B76BA4C977 ON tracker_history_io_last (tracker_history_io_id)');
        $this->addSql('CREATE INDEX IDX_A17041B7C54C8C93 ON tracker_history_io_last (type_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A17041B794A4C7D4C54C8C93 ON tracker_history_io_last (device_id, type_id)');
        $this->addSql('ALTER TABLE tracker_history_io_last ADD CONSTRAINT FK_A17041B794A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_history_io_last ADD CONSTRAINT FK_A17041B76BA4C977 FOREIGN KEY (tracker_history_io_id) REFERENCES tracker_history_io (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracker_history_io_last ADD CONSTRAINT FK_A17041B7C54C8C93 FOREIGN KEY (type_id) REFERENCES tracker_io_type (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE tracker_history_io_last');
    }
}
