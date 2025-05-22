<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200310123126 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE tracker_history ALTER alt DROP NOT NULL');
        $this->addSql('ALTER TABLE tracker_history ALTER angle DROP NOT NULL');
        $this->addSql('ALTER TABLE tracker_history ALTER priority DROP NOT NULL');
        $this->addSql('ALTER TABLE tracker_history ALTER lat DROP NOT NULL');
        $this->addSql('ALTER TABLE tracker_history ALTER lng DROP NOT NULL');

        $this->addSql('ALTER TABLE tracker_history_last ALTER alt DROP NOT NULL');
        $this->addSql('ALTER TABLE tracker_history_last ALTER angle DROP NOT NULL');
        $this->addSql('ALTER TABLE tracker_history_last ALTER priority DROP NOT NULL');
        $this->addSql('ALTER TABLE tracker_history_last ALTER lat DROP NOT NULL');
        $this->addSql('ALTER TABLE tracker_history_last ALTER lng DROP NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->updateLastTrackerHistory();
        $this->addSql('ALTER TABLE tracker_history_last ALTER priority SET NOT NULL');
        $this->addSql('ALTER TABLE tracker_history_last ALTER alt SET NOT NULL');
        $this->addSql('ALTER TABLE tracker_history_last ALTER angle SET NOT NULL');
        $this->addSql('ALTER TABLE tracker_history_last ALTER lat SET NOT NULL');
        $this->addSql('ALTER TABLE tracker_history_last ALTER lng SET NOT NULL');

        $this->updateTrackerHistory();
        $this->addSql('ALTER TABLE tracker_history ALTER priority SET NOT NULL');
        $this->addSql('ALTER TABLE tracker_history ALTER alt SET NOT NULL');
        $this->addSql('ALTER TABLE tracker_history ALTER angle SET NOT NULL');
        $this->addSql('ALTER TABLE tracker_history ALTER lat SET NOT NULL');
        $this->addSql('ALTER TABLE tracker_history ALTER lng SET NOT NULL');
    }

    private function updateTrackerHistory()
    {
        $this->addSql('UPDATE tracker_history SET priority = 0 WHERE priority IS NULL');
        $this->addSql('UPDATE tracker_history SET alt = 0 WHERE alt IS NULL');
        $this->addSql('UPDATE tracker_history SET angle = 0 WHERE angle IS NULL');
        $this->addSql('UPDATE tracker_history SET lat = 0 WHERE lat IS NULL');
        $this->addSql('UPDATE tracker_history SET lng = 0 WHERE lng IS NULL');
    }

    private function updateLastTrackerHistory()
    {
        $this->addSql('UPDATE tracker_history_last SET priority = 0 WHERE priority IS NULL');
        $this->addSql('UPDATE tracker_history_last SET alt = 0 WHERE alt IS NULL');
        $this->addSql('UPDATE tracker_history_last SET angle = 0 WHERE angle IS NULL');
        $this->addSql('UPDATE tracker_history_last SET lat = 0 WHERE lat IS NULL');
        $this->addSql('UPDATE tracker_history_last SET lng = 0 WHERE lng IS NULL');
    }
}
