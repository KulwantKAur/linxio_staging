<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231010093609 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE scheduled_report ADD timezone_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE scheduled_report ADD CONSTRAINT FK_68D1F39D3FE997DE FOREIGN KEY (timezone_id) REFERENCES time_zone (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_68D1F39D3FE997DE ON scheduled_report (timezone_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE scheduled_report DROP CONSTRAINT FK_68D1F39D3FE997DE');
        $this->addSql('DROP INDEX IDX_68D1F39D3FE997DE');
        $this->addSql('ALTER TABLE scheduled_report DROP timezone_id');
    }
}
