<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230724075633 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE area ADD type VARCHAR(100) DEFAULT \'custom\' NOT NULL');
        $this->addSql('ALTER TABLE area ADD external_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE area_group ADD type VARCHAR(100) DEFAULT \'custom\' NOT NULL');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE area DROP type');
        $this->addSql('ALTER TABLE area DROP external_id');
        $this->addSql('ALTER TABLE area_group DROP type');
    }
}
