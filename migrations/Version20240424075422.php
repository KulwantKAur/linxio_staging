<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240424075422 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE route_finish_area DROP CONSTRAINT FK_EEC5DE75BD0F409C');
        $this->addSql('ALTER TABLE route_finish_area DROP CONSTRAINT FK_EEC5DE7534ECB4E6');
        $this->addSql('ALTER TABLE route_finish_area ADD CONSTRAINT FK_EEC5DE75BD0F409C FOREIGN KEY (area_id) REFERENCES area (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE route_finish_area ADD CONSTRAINT FK_EEC5DE7534ECB4E6 FOREIGN KEY (route_id) REFERENCES route (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE route_start_area DROP CONSTRAINT FK_E65A9A4DBD0F409C');
        $this->addSql('ALTER TABLE route_start_area DROP CONSTRAINT FK_E65A9A4D34ECB4E6');
        $this->addSql('ALTER TABLE route_start_area ADD CONSTRAINT FK_E65A9A4DBD0F409C FOREIGN KEY (area_id) REFERENCES area (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE route_start_area ADD CONSTRAINT FK_E65A9A4D34ECB4E6 FOREIGN KEY (route_id) REFERENCES route (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EEC5DE7534ECB4E6BD0F409C ON route_finish_area (route_id, area_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E65A9A4D34ECB4E6BD0F409C ON route_start_area (route_id, area_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE route_start_area DROP CONSTRAINT fk_e65a9a4dbd0f409c');
        $this->addSql('ALTER TABLE route_start_area DROP CONSTRAINT fk_e65a9a4d34ecb4e6');
        $this->addSql('ALTER TABLE route_start_area ADD CONSTRAINT fk_e65a9a4dbd0f409c FOREIGN KEY (area_id) REFERENCES area (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE route_start_area ADD CONSTRAINT fk_e65a9a4d34ecb4e6 FOREIGN KEY (route_id) REFERENCES route (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP INDEX UNIQ_E65A9A4D34ECB4E6BD0F409C');
        $this->addSql('DROP INDEX UNIQ_EEC5DE7534ECB4E6BD0F409C');
    }
}
