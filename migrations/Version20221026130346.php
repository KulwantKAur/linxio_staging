<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221026130346 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE stripe_mandate_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('
            CREATE TABLE stripe_mandate (
                id INT NOT NULL, 
                mandate_id VARCHAR(255) NOT NULL, 
                payment_method_id VARCHAR(255) NOT NULL, 
                status VARCHAR(255) NOT NULL, 
                accepted_at INT NOT NULL, 
                PRIMARY KEY(id)
        )');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE stripe_mandate_id_seq CASCADE');
        $this->addSql('DROP TABLE stripe_mandate');
    }
}
