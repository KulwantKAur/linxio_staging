<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210622120217 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE users_areas (user_group_id INT NOT NULL, area_id INT NOT NULL, PRIMARY KEY(user_group_id, area_id))');
        $this->addSql('CREATE INDEX IDX_BFC716151ED93D47 ON users_areas (user_group_id)');
        $this->addSql('CREATE INDEX IDX_BFC71615BD0F409C ON users_areas (area_id)');
        $this->addSql('CREATE TABLE users_area_groups (user_group_id INT NOT NULL, area_group_id INT NOT NULL, PRIMARY KEY(user_group_id, area_group_id))');
        $this->addSql('CREATE INDEX IDX_6153F4001ED93D47 ON users_area_groups (user_group_id)');
        $this->addSql('CREATE INDEX IDX_6153F4003D5B878F ON users_area_groups (area_group_id)');
        $this->addSql('CREATE TABLE users_permissions (user_group_id INT NOT NULL, permission_id INT NOT NULL, PRIMARY KEY(user_group_id, permission_id))');
        $this->addSql('CREATE INDEX IDX_DA58F09D1ED93D47 ON users_permissions (user_group_id)');
        $this->addSql('CREATE INDEX IDX_DA58F09DFED90CCA ON users_permissions (permission_id)');
        $this->addSql('ALTER TABLE users_areas ADD CONSTRAINT FK_BFC716151ED93D47 FOREIGN KEY (user_group_id) REFERENCES user_group (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users_areas ADD CONSTRAINT FK_BFC71615BD0F409C FOREIGN KEY (area_id) REFERENCES area (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users_area_groups ADD CONSTRAINT FK_6153F4001ED93D47 FOREIGN KEY (user_group_id) REFERENCES user_group (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users_area_groups ADD CONSTRAINT FK_6153F4003D5B878F FOREIGN KEY (area_group_id) REFERENCES area_group (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users_permissions ADD CONSTRAINT FK_DA58F09D1ED93D47 FOREIGN KEY (user_group_id) REFERENCES user_group (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users_permissions ADD CONSTRAINT FK_DA58F09DFED90CCA FOREIGN KEY (permission_id) REFERENCES permission (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE users_areas');
        $this->addSql('DROP TABLE users_area_groups');
        $this->addSql('DROP TABLE users_permissions');
    }
}
