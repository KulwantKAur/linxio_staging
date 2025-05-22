<?php declare(strict_types=1);

namespace Application\Migrations;

use App\Entity\DigitalForm;
use App\Entity\Setting;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * migrate settings from global to forms
 */
final class Version20210226140804 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql(
            'UPDATE digital_forms
                    SET inspection_period = (
                        SELECT s.value #>>\'{}\'
                        FROM setting AS s
                        WHERE digital_forms.team_id = s.team_id
                          AND s.role_id IS NULL
                          AND s.user_id IS NULL
                          AND s.name = ?
                    )
                  WHERE type = ?
                    AND inspection_period IS NULL;',
            [Setting::INSPECTION_FORM_PERIOD, DigitalForm::TYPE_INSPECTION]
        );
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
