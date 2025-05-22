<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200210112923 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE plan_permission (plan_id INT NOT NULL, permission_id INT NOT NULL, PRIMARY KEY(plan_id, permission_id))');
        $this->addSql('CREATE INDEX IDX_8D24A8BCE899029B ON plan_permission (plan_id)');
        $this->addSql('CREATE INDEX IDX_8D24A8BCFED90CCA ON plan_permission (permission_id)');
        $this->addSql('ALTER TABLE plan_permission ADD CONSTRAINT FK_8D24A8BCE899029B FOREIGN KEY (plan_id) REFERENCES plan (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE plan_permission ADD CONSTRAINT FK_8D24A8BCFED90CCA FOREIGN KEY (permission_id) REFERENCES permission (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE plan_permission');
    }
}