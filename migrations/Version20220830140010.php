<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220830140010 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE stripe_secret_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('
            CREATE TABLE stripe_secret 
            (
                id INT NOT NULL, 
                team_id INT NOT NULL, 
                secret_key VARCHAR(255) NOT NULL,
                public_key VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )
        ');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F4EF95D9296CD8AE ON stripe_secret (team_id)');
        $this->addSql('
            ALTER TABLE stripe_secret 
            ADD CONSTRAINT FK_F4EF95D9296CD8AE 
            FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
        $this->addSql('ALTER TABLE client ADD stripe_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE stripe_secret_id_seq CASCADE');
        $this->addSql('DROP TABLE stripe_secret');
        $this->addSql('ALTER TABLE client DROP stripe_id');
    }
}
