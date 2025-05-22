<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200319103617 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE driving_behavior ADD type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE driving_behavior ALTER lng DROP NOT NULL');
        $this->addSql('ALTER TABLE driving_behavior ALTER lat DROP NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->updateDrivingBehavior();
        $this->addSql('ALTER TABLE driving_behavior DROP type_id');
        $this->addSql('ALTER TABLE driving_behavior ALTER lng SET NOT NULL');
        $this->addSql('ALTER TABLE driving_behavior ALTER lat SET NOT NULL');
    }

    private function updateDrivingBehavior()
    {
        $this->addSql('UPDATE driving_behavior SET lat = 0 WHERE lat IS NULL');
        $this->addSql('UPDATE driving_behavior SET lng = 0 WHERE lng IS NULL');
    }
}
