<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220321085834 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE billing_entity_history_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE billing_entity_history (id BIGINT NOT NULL, entity_id INT NOT NULL, entity VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, date_from TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, date_to TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, data JSON DEFAULT NULL, PRIMARY KEY(id))');

        $this->addSql('ALTER TABLE device ADD is_deactivated BOOLEAN DEFAULT \'false\' NOT NULL');
        $this->addSql('ALTER TABLE device ADD is_unavailable BOOLEAN DEFAULT \'false\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE billing_entity_history');

        $this->addSql('ALTER TABLE device DROP is_deactivated');
        $this->addSql('ALTER TABLE device DROP is_unavailable');
    }
}
