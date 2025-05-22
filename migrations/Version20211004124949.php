<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211004124949 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE plan_role_permission DROP CONSTRAINT FK_8F17CFF8FED90CCA');
        $this->addSql('ALTER TABLE plan_role_permission ADD CONSTRAINT FK_8F17CFF8FED90CCA FOREIGN KEY (permission_id) REFERENCES permission (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE plan_role_permission DROP CONSTRAINT fk_8f17cff8fed90cca');
        $this->addSql('ALTER TABLE plan_role_permission ADD CONSTRAINT fk_8f17cff8fed90cca FOREIGN KEY (permission_id) REFERENCES permission (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
