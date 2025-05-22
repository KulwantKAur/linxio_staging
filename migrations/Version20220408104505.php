<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220408104505 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql('ALTER TABLE billing_plan ADD team_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE billing_plan ADD plan_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE billing_plan DROP is_default');
        $this->addSql('ALTER TABLE billing_plan ADD CONSTRAINT FK_A22865BA296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE billing_plan ADD CONSTRAINT FK_A22865BAE899029B FOREIGN KEY (plan_id) REFERENCES plan (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A22865BA296CD8AE ON billing_plan (team_id)');
        $this->addSql('CREATE INDEX IDX_A22865BAE899029B ON billing_plan (plan_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

        $this->addSql('ALTER TABLE billing_plan DROP CONSTRAINT FK_A22865BA296CD8AE');
        $this->addSql('ALTER TABLE billing_plan DROP CONSTRAINT FK_A22865BAE899029B');
        $this->addSql('DROP INDEX UNIQ_A22865BA296CD8AE');
        $this->addSql('DROP INDEX IDX_A22865BAE899029B');
        $this->addSql('ALTER TABLE billing_plan ADD is_default BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE billing_plan DROP team_id');
        $this->addSql('ALTER TABLE billing_plan DROP plan_id');
    }
}
