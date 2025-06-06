<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221108093810 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE platform_setting ADD host_app VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE platform_setting ADD host_api VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE platform_setting ADD host_track VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE platform_setting ADD host_messenger VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE platform_setting DROP host_app');
        $this->addSql('ALTER TABLE platform_setting DROP host_api');
        $this->addSql('ALTER TABLE platform_setting DROP host_track');
        $this->addSql('ALTER TABLE platform_setting DROP host_messenger');
    }
}
