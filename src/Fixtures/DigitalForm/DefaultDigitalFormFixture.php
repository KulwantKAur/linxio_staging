<?php

declare(strict_types = 1);

namespace App\Fixtures\DigitalForm;

use App\Entity\DigitalForm;
use App\Entity\DigitalFormSchedule;
use App\Entity\DigitalFormScheduleRecipient;
use App\Entity\DigitalFormStep;
use App\Entity\User;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class DefaultDigitalFormFixture extends BaseFixture implements FixtureGroupInterface
{
    /** @var string */
    public const FORM_NAME = 'Default pre-use Inspection';


    public static function getGroups(): array
    {
        return [FixturesTypes::TESTING];
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $manager = $this->prepareEntityManager($manager);
        $admin = $manager->getRepository(User::class)->findOneBy(
            ['surname' => 'Admin'],
            ['id' => 'ASC']
        );

        if ($admin === null) {
            return;
        }

        // disable old default forms
        $forms = $manager->getRepository(DigitalForm::class)->findBy(['title' => self::FORM_NAME]);
        foreach ($forms as $form) {
            $form->setActive(false);
            $manager->persist($form);
        }

        $manager->flush();

        // form must be present in single instance
        $defaultForm = $manager->getRepository(DigitalForm::class)->findOneBy(['title' => DefaultDigitalFormFixture::FORM_NAME, 'team' => null, 'active' => true], ['id' => 'ASC']);
        if ($defaultForm !== null) {
            return;
        }

        $form = new DigitalForm();
        $form->setCreatedBy($admin);
        $form->setType(DigitalForm::TYPE_INSPECTION);
        $form->setTitle(self::FORM_NAME);
        $form->setActive(true);
        $form->setStatus(DigitalForm::STATUS_ACTIVE);
        $form->setInspectionPeriod(DigitalForm::INSPECTION_PERIOD_EVERY_TIME);
        $manager->persist($form);

        foreach ($this->getFormSteps() as $step) {
            $step->setDigitalForm($form);
            $manager->persist($step);
        }

        $schedule = new DigitalFormSchedule();
        $schedule->setDigitalForm($form);
        $schedule->setCreatedBy($admin);
        $schedule->setIsDefault(true);
//        $schedule->setTimeFrom(new \Datetime('00:00:00'));
//        $schedule->setTimeTo(new \Datetime('23:59:00'));
//        $schedule->setDays(DigitalFormSchedule::VALID_DAYS);
        $manager->persist($schedule);

        $recipient = new DigitalFormScheduleRecipient();
        $recipient->setDigitalFormSchedule($schedule);
        $recipient->setType(DigitalFormScheduleRecipient::TYPE_ANY);
        $recipient->setValue([]);
        $recipient->setAdditionalType(DigitalFormScheduleRecipient::TYPE_ANY);
        $recipient->setAdditionalValue([]);
        $manager->persist($recipient);

        $manager->flush();
    }


    private function getFormSteps(): array
    {
        $steps = [];

        $step = new DigitalFormStep();
        $step->setStepOrder(1);
        $step->setTitle('Are you fit to operate this vehicle?');
        $step->setDescription('You are required to have a valid drivers license for this vehicle type, and must not be under the influence of any illicit drug or any medication that could negatively interfere with motor skills.');
        $step->setOptions(json_decode('{"type": "list.single", "items": [{"index": 1, "label": "Yes"}, {"index": 2, "label": "No"}], "failIndexes": [2]}', true));
        $steps[] = $step;

        $step = new DigitalFormStep();
        $step->setStepOrder(2);
        $step->setTitle('Odometer');
        $step->setDescription('Please check and enter the current odometer reading for this vehicle.');
        $step->setOptions(json_decode('{"type": "odometer", "range": 50, "default": 0}', true));
        $steps[] = $step;

        $step = new DigitalFormStep();
        $step->setStepOrder(3);
        $step->setTitle('Wheels & Tyres');
        $step->setDescription('Check as much of each tyre/wheel as you can see. There must be:
1. Minimum tread depth of 1mm;
2. Sufficient inflation of each tyre;
3. No deep cuts in the sidewall;
4. No cord visible anywhere on tyre;
5. And no missing or insecure wheel-nuts.');
        $step->setOptions(json_decode('{"type": "list.single", "items": [{"index": 1, "label": "Pass"}, {"index": 2, "label": "Fail"}], "failIndexes": [2]}', true));
        $steps[] = $step;

        $step = new DigitalFormStep();
        $step->setStepOrder(4);
        $step->setTitle('Body check');
        $step->setDescription('Make sure that:
1. All fastening devices are present, complete, secure and in working order;
2. Cab doors and trailer doors are secure when closed;
3. No body panels on tractor unit or trailer are loose and in danger of falling off;
4. No landing legs, where fitted, are likely to fall from the vehicle.');
        $step->setOptions(json_decode('{"type": "list.single", "items": [{"index": 1, "label": "Pass"}, {"index": 2, "label": "Fail"}], "failIndexes": [2]}', true));
        $steps[] = $step;

        $step = new DigitalFormStep();
        $step->setStepOrder(5);
        $step->setTitle('Lights and indicators check');
        $step->setDescription('Check that:
1. All lights and indicators work correctly;
2. All lenses are present, clean and are of the correct colour;
3. Stop lamps come on when the service brake is applied and go out when released;
4. Marker lights are present and work (where applicable); and
5. All dashboard warning lamps work correctly (e.g. the ABS warning lamp, full headlamp warning lamp, parking brake warning lamp, etc.).');
        $step->setOptions(json_decode('{"type": "list.single", "items": [{"index": 1, "label": "Pass"}, {"index": 2, "label": "Fail"}], "failIndexes": [2]}', true));
        $steps[] = $step;

        $step = new DigitalFormStep();
        $step->setStepOrder(6);
        $step->setTitle('Fuel Level Check');
        $step->setDescription('Check fuel level is adequate and note the approximate level');
        $step->setOptions(json_decode('{"type": "list.single", "items": [{"index": 1, "label": "Pass"}, {"index": 2, "label": "Fail"}], "failIndexes": [2]}', true));
        $steps[] = $step;

        $step = new DigitalFormStep();
        $step->setStepOrder(7);
        $step->setTitle('Windscreen, Mirrors & Wipers');
        $step->setDescription('1. Check windows & mirrors for security, damage and grime;
2. Check wiper blade and windscreen washer operation to ensure clear vision.');
        $step->setOptions(json_decode('{"type": "list.single", "items": [{"index": 1, "label": "Pass"}, {"index": 2, "label": "Fail"}], "failIndexes": [2]}', true));
        $steps[] = $step;

        $step = new DigitalFormStep();
        $step->setStepOrder(8);
        $step->setTitle('Brakes');
        $step->setDescription('1. Check Brake failure indicator;
2. Check pressure/vacuum gauges;
3. Bleed off contaminants from air tanks (full air & air over hydraulic systems only on rigid & articulated combinations.  Buses & coaches are exempted due to the nature of their systems).');
        $step->setOptions(json_decode('{"type": "list.single", "items": [{"index": 1, "label": "Pass"}, {"index": 2, "label": "Fail"}], "failIndexes": [2]}', true));
        $steps[] = $step;

        $step = new DigitalFormStep();
        $step->setStepOrder(9);
        $step->setTitle('Signature');
        $step->setDescription('');
        $step->setOptions(json_decode('{"type": "signature"}', true));
        $steps[] = $step;

        return $steps;
    }
}
