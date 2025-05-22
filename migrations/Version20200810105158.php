<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200810105158 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE inspection_form ADD is_default BOOLEAN DEFAULT \'false\' NOT NULL');
        $this->addSql('CREATE TABLE inspection_form_teams (inspection_form_id INT NOT NULL, team_id INT NOT NULL, PRIMARY KEY(inspection_form_id, team_id))');
        $this->addSql('CREATE INDEX IDX_70C915C4F7A3886F ON inspection_form_teams (inspection_form_id)');
        $this->addSql('CREATE INDEX IDX_70C915C4296CD8AE ON inspection_form_teams (team_id)');
        $this->addSql('ALTER TABLE inspection_form_teams ADD CONSTRAINT FK_70C915C4F7A3886F FOREIGN KEY (inspection_form_id) REFERENCES inspection_form (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE inspection_form_teams ADD CONSTRAINT FK_70C915C4296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE inspection_form DROP CONSTRAINT fk_789a9e53296cd8ae');
        $this->addSql('DROP INDEX idx_789a9e53296cd8ae');
        $this->addSql('ALTER TABLE inspection_form DROP team_id');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE inspection_form DROP is_default');
        $this->addSql('DROP TABLE inspection_form_teams');
        $this->addSql('ALTER TABLE inspection_form ADD team_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE inspection_form ADD CONSTRAINT fk_789a9e53296cd8ae FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_789a9e53296cd8ae ON inspection_form (team_id)');

    }
}
