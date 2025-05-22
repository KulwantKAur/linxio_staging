<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221220120922 extends AbstractMigration
{
    private function markChatsAsIndividual(): void
    {
        $this->addSql('UPDATE chat SET is_individual = true
            FROM (SELECT c.id AS c_id
                FROM chat c
                    LEFT JOIN chat_users cu ON cu.chat_id = c.id
                GROUP BY c_id
                HAVING COUNT(cu) = 2
            ) AS chat_sub
            WHERE chat.id = chat_sub.c_id'
        );
    }

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chat ADD is_individual BOOLEAN DEFAULT \'false\' NOT NULL');
        $this->markChatsAsIndividual();
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chat DROP is_individual');
    }
}
