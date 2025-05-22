<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210331115447 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE digital_form_schedule ALTER COLUMN time_from DROP NOT NULL;');
        $this->addSql('ALTER TABLE digital_form_schedule ALTER COLUMN time_from SET DEFAULT NULL;');
        $this->addSql('ALTER TABLE digital_form_schedule ALTER COLUMN time_to DROP NOT NULL;');
        $this->addSql('ALTER TABLE digital_form_schedule ALTER COLUMN time_to SET DEFAULT NULL;');
        $this->addSql('ALTER TABLE digital_form_schedule ALTER COLUMN days DROP NOT NULL;');
        $this->addSql('ALTER TABLE digital_form_schedule ADD COLUMN day_of_month SMALLINT DEFAULT NULL;');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE digital_form_schedule ALTER COLUMN time_from SET NOT NULL;');
        $this->addSql('ALTER TABLE digital_form_schedule ALTER COLUMN time_from DROP DEFAULT;');
        $this->addSql('ALTER TABLE digital_form_schedule ALTER COLUMN time_to SET NOT NULL;');
        $this->addSql('ALTER TABLE digital_form_schedule ALTER COLUMN time_to DROP DEFAULT;');
        $this->addSql('ALTER TABLE digital_form_schedule ALTER COLUMN days SET NOT NULL;');
        $this->addSql('ALTER TABLE digital_form_schedule DROP COLUMN day_of_month;');
    }
}
