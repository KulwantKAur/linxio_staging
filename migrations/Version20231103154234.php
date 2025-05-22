<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231103154234 extends AbstractMigration
{
    private string $job1Name = 'remove_route_duplicates';
    private string $job2Name = 'remove_speeding_duplicates';

    public function up(Schema $schema) : void
    {
        $this->addSql('SELECT cron.schedule(\'' . $this->job1Name . '\', \'*/5 * * * *\', $$DELETE FROM route r WHERE r.id IN (SELECT id FROM (SELECT id, ROW_NUMBER() OVER (PARTITION BY started_at, device_id ORDER BY finished_at DESC) AS row_num FROM route WHERE created_at > NOW() - INTERVAL \'6\' HOUR AND created_at < NOW() - INTERVAL \'1\' MINUTE) r_sub WHERE r_sub.row_num > 1);$$);');
        $this->addSql('SELECT cron.schedule(\'' . $this->job2Name . '\', \'00 14 * * *\', $$DELETE FROM speeding s WHERE s.id IN (SELECT id FROM (SELECT id, ROW_NUMBER() OVER (PARTITION BY started_at, device_id ORDER BY finished_at DESC) AS row_num FROM speeding WHERE started_at >= NOW() - INTERVAL \'1\' MONTH) s_sub WHERE s_sub.row_num > 1);$$);');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('SELECT cron.unschedule(\'' . $this->job2Name . '\')');
        $this->addSql('SELECT cron.unschedule(\'' . $this->job1Name . '\')');
    }
}
