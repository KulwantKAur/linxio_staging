<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211109100738 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE platform_setting ADD link_knowledge_base VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE platform_setting ADD intercom_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE platform_setting DROP link_knowledge_base');
        $this->addSql('ALTER TABLE platform_setting DROP intercom_id');
    }
}
