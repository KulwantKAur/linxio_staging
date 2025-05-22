<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191121102425 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE reminder_category_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE reminder_category (id INT NOT NULL, team_id INT DEFAULT NULL, created_by BIGINT DEFAULT NULL, updated_by BIGINT DEFAULT NULL, name VARCHAR(255) NOT NULL, status VARCHAR(50) NOT NULL, createdAt TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updatedAt TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_643F1E57296CD8AE ON reminder_category (team_id)');
        $this->addSql('CREATE INDEX IDX_643F1E57DE12AB56 ON reminder_category (created_by)');
        $this->addSql('CREATE INDEX IDX_643F1E5716FE72E1 ON reminder_category (updated_by)');
        $this->addSql('ALTER TABLE reminder_category ADD CONSTRAINT FK_643F1E57296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reminder_category ADD CONSTRAINT FK_643F1E57DE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reminder_category ADD CONSTRAINT FK_643F1E5716FE72E1 FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reminder ADD category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reminder ADD CONSTRAINT FK_40374F4012469DE2 FOREIGN KEY (category_id) REFERENCES reminder_category (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_40374F4012469DE2 ON reminder (category_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE reminder DROP CONSTRAINT FK_40374F4012469DE2');
        $this->addSql('DROP SEQUENCE reminder_category_id_seq CASCADE');
        $this->addSql('DROP TABLE reminder_category');
        $this->addSql('DROP INDEX IDX_40374F4012469DE2');
        $this->addSql('ALTER TABLE reminder DROP category_id');
    }
}
