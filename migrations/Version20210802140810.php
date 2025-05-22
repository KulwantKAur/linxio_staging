<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210802140810 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs

//        $this->addSql('ALTER TABLE event_log ALTER COLUMN "details" TYPE jsonb USING "details"::jsonb;');
//        $this->addSql('CREATE INDEX event_log_details_index ON event_log USING GIN(details)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

//        $this->addSql('DROP INDEX event_log_details_index');
    }
}
