<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201026121447 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE INDEX tracker_history_is_calculated_index ON tracker_history (is_calculated)');
        $this->addSql('CREATE INDEX tracker_history_is_calculated_idling_index ON tracker_history (is_calculated_idling)');
        $this->addSql('CREATE INDEX tracker_history_is_calculated_speeding_index ON tracker_history (is_calculated_speeding)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX tracker_history_is_calculated_index');
        $this->addSql('DROP INDEX tracker_history_is_calculated_idling_index');
        $this->addSql('DROP INDEX tracker_history_is_calculated_speeding_index');
    }
}
