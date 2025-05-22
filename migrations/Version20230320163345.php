<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230320163345 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE device ADD professional_install BOOLEAN DEFAULT \'false\' NOT NULL');
        $this->addSql('ALTER TABLE device ADD ownership VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE device DROP ownership');
        $this->addSql('ALTER TABLE device DROP professional_install');
    }
}
