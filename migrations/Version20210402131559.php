<?php declare(strict_types=1);

namespace Application\Migrations;

use App\Entity\DigitalForm;
use App\Entity\Setting;
use App\Fixtures\DigitalForm\DefaultDigitalFormFixture;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * set inspection period for default form
 */
final class Version20210402131559 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql(
            'UPDATE digital_forms
                    SET inspection_period = ?
                  WHERE type = ?
                    AND title = ?
                    AND team_id IS NULL
                    AND active = TRUE
                    AND inspection_period IS NULL;',
            [
                DigitalForm::INSPECTION_PERIOD_EVERY_TIME,
                DigitalForm::TYPE_INSPECTION,
                DefaultDigitalFormFixture::FORM_NAME
            ]
        );
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql(
            'UPDATE digital_forms
                    SET inspection_period = NULL
                  WHERE type = ?
                    AND title = ?
                    AND team_id IS NULL
                    AND active = TRUE
                    AND inspection_period = ?;',
            [
                DigitalForm::TYPE_INSPECTION,
                DefaultDigitalFormFixture::FORM_NAME,
                DigitalForm::INSPECTION_PERIOD_EVERY_TIME
            ]
        );
    }
}
