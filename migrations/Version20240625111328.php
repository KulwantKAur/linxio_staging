<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240625111328 extends AbstractMigration
{
    private string $jobName = 'delete_tracker_payload_streamax';

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tracker_payload_streamax (id BIGSERIAL NOT NULL, payload TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, is_processed BOOLEAN DEFAULT \'false\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX tracker_payload_streamax_created_at_idx ON tracker_payload_streamax (created_at)');
        $this->addSql('SELECT cron.schedule(\'' . $this->jobName . '\', \'0 13 * * *\', $$DELETE FROM tracker_payload_streamax WHERE created_at < NOW() - INTERVAL \'14\' DAY;$$);');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('SELECT cron.unschedule(\'' . $this->jobName . '\')');
        $this->addSql('DROP TABLE tracker_payload_streamax');
    }
}
