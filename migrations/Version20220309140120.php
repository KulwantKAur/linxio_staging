<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220309140120 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE role SET name = \'sales_rep\' where name = \'client_manager\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE role SET name = \'client_manager\' where name = \'sales_rep\'');
    }
}
