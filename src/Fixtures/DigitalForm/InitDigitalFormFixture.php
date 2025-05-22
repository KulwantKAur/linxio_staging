<?php

declare(strict_types = 1);

namespace App\Fixtures\DigitalForm;

use App\Entity\DigitalForm;
use App\Entity\DigitalFormAnswer;
use App\Entity\DigitalFormAnswerStep;
use App\Entity\DigitalFormStep;
use App\Entity\File;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use App\Fixtures\Users\InitDemoUsersFixtures;
use App\Fixtures\Vehicles\InitVehiclesFixture;
use App\Service\DigitalForm\DigitalFormStepFactory;
use App\Service\DigitalForm\Entity\Condition;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

class InitDigitalFormFixture extends BaseFixture implements DependentFixtureInterface, FixtureGroupInterface
{
    /** @var string */
    public const FORM_REFERENCE_0 = 'digitalForms0';
    public const FORM_REFERENCE_1 = 'digitalForms1';
    public const FORM_REFERENCE_2 = 'digitalForms2';

    /** @var DigitalFormStepFactory */
    private $digitalFormStepFactory;

    /** @var User */
    private $admin;

    /** @var Vehicle */
    private $vehicle;

    /** @var EntityManager */
    private $manager;


    public function __construct(DigitalFormStepFactory $digitalFormStepFactory)
    {
        $this->digitalFormStepFactory = $digitalFormStepFactory;
    }

    public function getDependencies(): array
    {
        return array(
            InitDemoUsersFixtures::class,
            InitVehiclesFixture::class,
        );
    }

    /**
     * @return array
     */
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
        $this->manager = $this->prepareEntityManager($manager);
        $this->admin = $this->manager->getRepository(User::class)->findOneBy([
            'name' => 'Acme',
        ]);
        $this->vehicle = $this->manager->getRepository(Vehicle::class)->findOneBy([
            'team' => $this->admin->getTeam(),
        ]);

        $digitalForm = [];
        $digitalFormSteps = [];
        for ($i = 0; $i < 3; $i++) {
            $item = new DigitalForm();
            $item->setCreatedBy($this->admin);
            $item->setTeam($this->admin->getTeam());
            $item->setType(DigitalForm::TYPE_INSPECTION);
            $item->setTitle('Form title #' . rand(0, 10000));
            $item->setActive(true);
            $item->setStatus(DigitalForm::STATUS_ACTIVE);
            $item->setInspectionPeriod(DigitalForm::INSPECTION_PERIOD_SHOW_ALWAYS);

            $this->manager->persist($item);
            $digitalForm[] = $item;
            $digitalFormSteps[$i] = [
                'form' => $item,
                'steps' => [],
            ];
        }

        $this->addReference(self::FORM_REFERENCE_0, $digitalForm[0]);
        $this->addReference(self::FORM_REFERENCE_1, $digitalForm[1]);
        $this->addReference(self::FORM_REFERENCE_2, $digitalForm[2]);

        foreach ($digitalForm as $key => $form) {
            $length = 29;
            if ($key > 1) {
                $length = 1;
            }

            for ($i = 0; $i < $length; $i++) {
                $item = new DigitalFormStep();
                $item->setDigitalForm($form);
                $item->setStepOrder($i + 1);
                $item->setTitle($this->getStepTitle($key, $i));
                $item->setDescription($this->getStepDescription($key, $i));
                $item->setCondition($this->getStepConditions($key, $i));
                $item->setOptions($this->getStep($key, $i));

                $this->manager->persist($item);
                $digitalFormSteps[$key]['steps'][] = $item;
            }
        }

        foreach ($digitalFormSteps as $formKey => $row) {
            $answer = new DigitalFormAnswer();
            $answer->setDigitalForm($row['form']);
            $answer->setUser($this->admin);
            $answer->setVehicle($this->vehicle);
            $this->manager->persist($answer);

            foreach ($row['steps'] as $key => $step) {
                $stepAnswer = new DigitalFormAnswerStep();
                $stepAnswer->setDigitalFormAnswer($answer);
                $stepAnswer->setDigitalFormStep($step);
                $stepAnswer->setValue($this->getStepAnswerValue($formKey, $key));
                $stepAnswer->setFile($this->getFile($formKey, $key));
                $stepAnswer->setDuration(rand(0, 5));
                $stepAnswer->setAdditionalNote($this->getStepTitle($formKey, $key));
                $stepAnswer->setAdditionalFile($this->getFile($formKey, $key));

                $isSelectType = in_array($step->getOptions()->getType(), [DigitalFormStepFactory::TYPE_LIST_SINGLE, DigitalFormStepFactory::TYPE_LIST_MULTI]);
                if ($isSelectType && is_array($step->getOptions()->getFailIndexes())) {
                    $stepAnswer->setIsPass(!in_array($stepAnswer->getValue(), $step->getOptions()->getFailIndexes()));
                }

                $this->manager->persist($stepAnswer);

                $answer->addDigitalFormAnswerStep($stepAnswer);
            }
        }

        $this->manager->flush();
    }

    private function getStepTitle($key, $index)
    {
        if ($key === 0) {
            $title = array_merge(
                array_fill(0, 6, 'Wellness check – Driver'),
                array_fill(0, 7, 'Wellness check – Partner'),
                array_fill(0, 14, 'Vehicle Check'),
                ['Signature', 'Signature - Partner']
            );
        } else {
            $title = array_merge(
                array_fill(0, 7, 'Step title 0-7 pack'),
                array_fill(0, 7, 'Step title 8-14 pack'),
                array_fill(0, 7, 'Step title 15-21 pack')
            );
        }

        return $title[$index % count($title)];
    }

    private function getStepDescription($key, $index)
    {
        if ($key === 0) {
            $description = [
                'Do you have a cough?',
                'Do you have a sore throat?',
                'Are you experiencing a shortness of breath?',
                'Do you have a runny nose?',
                'Do you have a temperature of 37.5 degrees Celsius or over?',
                'Have you travelled to any part of Victoria or been in contact with someone from Victoria in the last 14 days?',
                'Is partner with you?',
                'Do you have a cough?',
                'Do you have a sore throat?',
                'Are you experiencing a shortness of breath?',
                'Do you have a runny nose?',
                'Do you have a temperature of 37.5 degrees Celsius or over?',
                'Have you travelled to any part of Victoria or been in contact with someone from Victoria in the last 14 days?',
                'Check for any new damage unreported',
                'Interior clean and free from rubbish',
                'Resus bag fully stocked and security tag still present',
                'Removed, tested and returned stretchers x2',
                'O2 bottles >50% capacity (On board and portable)',
                'Check AED has ‘green tick’ and ready for use',
                'Rotate/Swap Stryker Stretcher battery from charger to stretcher',
                'Check for all manual handling equipment present',
                'Clinell wipes (Red and Green)',
                'Hand sanitizer',
                'Appropriately sized gloves',
                'Do you agree you have check all above equipment and are fully prepared for shift?',
                'Odometer reading',
                'No other comments or concerns*',
            ];
        } else {
            $description = array_merge(
                array_fill(0, 7, 'Step description 0-7 pack'),
                array_fill(0, 7, 'Step description 8-14 pack'),
                array_fill(0, 7, 'Step description 15-21 pack')
            );
        }

        return $description[$index % count($description)];
    }

    private function getStepConditions($key, $index)
    {
        if ($key === 0) {
            $conditions = array_merge(
                array_fill(0, 7, null),
                array_fill(0, 6, new Condition(7, Condition::OPERATOR_EQUAL, 1)),
                array_fill(0, 15, null),
                [new Condition(7, Condition::OPERATOR_EQUAL, 1)]
            );
        } elseif ($key === 2) {
            $conditions = [null];
        } else {
            $conditions = array_merge(
                array_fill(0, 8, null),
                array_fill(0, 7, new Condition(8, Condition::OPERATOR_MORE_OR_EQUAL, 1)),
                array_fill(0, 3, null),
                array_fill(0, 2, new Condition(10, Condition::OPERATOR_LESS, 100000)),
                array_fill(0, 7, null)
            );
        }

        return $conditions[$index % count($conditions)];
    }

    private function getStep($key, $index)
    {
        if ($key === 0) {
            $step1 = $this->digitalFormStepFactory->create(DigitalFormStepFactory::TYPE_LIST_SINGLE);
            $step1->setItems([['index' => 1, 'label' => 'Yes'], ['index' => 2, 'label' => 'No']]);
            $step1->setFailIndexes([1]);

            $step2 = $this->digitalFormStepFactory->create(DigitalFormStepFactory::TYPE_LIST_SINGLE);
            $step2->setItems([['index' => 1, 'label' => 'Yes'], ['index' => 2, 'label' => 'No']]);

            $step3 = $this->digitalFormStepFactory->create(DigitalFormStepFactory::TYPE_LIST_SINGLE);
            $step3->setItems([['index' => 1, 'label' => 'Pass'], ['index' => 2, 'label' => 'Fail']]);
            $step3->setFailIndexes([2]);

            $step4 = $this->digitalFormStepFactory->create(DigitalFormStepFactory::TYPE_LIST_SINGLE);
            $step4->setItems([['index' => 1, 'label' => 'Yes'], ['index' => 2, 'label' => 'No']]);
            $step4->setFailIndexes([2]);

            $step5 = $this->digitalFormStepFactory->create(DigitalFormStepFactory::TYPE_NUMBER_INT);
            $step5->setMin(120000);
            $step5->setMax(140000);

            $step6 = $this->digitalFormStepFactory->create(DigitalFormStepFactory::TYPE_TEXT_MULTI);
            $step6->setDefault('Placeholder ...');
            $step6->setMin(1);
            $step6->setMax(10000);

            $step7 = $this->digitalFormStepFactory->create(DigitalFormStepFactory::TYPE_SIGNATURE);

            $steps = array_merge(
                array_fill(0, 6, $step1),
                [$step2],
                array_fill(0, 6, $step1),
                [$step3],
                array_fill(0, 11, $step4),
                [$step5, $step6, $step7, $step7]
            );
        } elseif ($key === 2) {
            $step1 = $this->digitalFormStepFactory->create(DigitalFormStepFactory::TYPE_ODOMETER);
            $step1->setDefault(111111);

            $steps = [$step1];
        } else {
            $step1 = $this->digitalFormStepFactory->create(DigitalFormStepFactory::TYPE_DATE);
            $step1->setDefault('now');
            $step1->setMin(86400);
            $step1->setMax(8640000);

            $step2 = $this->digitalFormStepFactory->create(DigitalFormStepFactory::TYPE_DATETIME);
            $step2->setDefault((new \Datetime())->format('c'));
            $step2->setMin(86400);
            $step2->setMax(8640000);

            $step3 = $this->digitalFormStepFactory->create(DigitalFormStepFactory::TYPE_FILE);

            $step4 = $this->digitalFormStepFactory->create(DigitalFormStepFactory::TYPE_IMAGE);

            $step5 = $this->digitalFormStepFactory->create(DigitalFormStepFactory::TYPE_LIST_MULTI);
            $step5->setItems([['index' => 1, 'label' => 'Yes'], ['index' => 2, 'label' => 'No'], ['index' => 3, 'label' => 'Maybe']]);
            $step5->setFailIndexes([1, 3]);

            $step6 = $this->digitalFormStepFactory->create(DigitalFormStepFactory::TYPE_NUMBER_FLOAT);
            $step6->setMin(1.11);
            $step6->setMax(333.33);

            $step7 = $this->digitalFormStepFactory->create(DigitalFormStepFactory::TYPE_LIST_SINGLE);
            $step7->setItems([['index' => 1, 'label' => 'Pass'], ['index' => 2, 'label' => 'Fail']]);

            $step8 = $this->digitalFormStepFactory->create(DigitalFormStepFactory::TYPE_LIST_SINGLE);
            $step8->setItems([['index' => 1, 'label' => 'Pass'], ['index' => 2, 'label' => 'Fail']]);
            $step8->setFailIndexes([1]);

            $step9 = $this->digitalFormStepFactory->create(DigitalFormStepFactory::TYPE_NUMBER_INT);
            $step9->setMin(120000);
            $step9->setMax(140000);

            $step10 = $this->digitalFormStepFactory->create(DigitalFormStepFactory::TYPE_SIGNATURE);

            $step11 = $this->digitalFormStepFactory->create(DigitalFormStepFactory::TYPE_TEXT_MULTI);
            $step11->setDefault('Step placeholder ...');
            $step11->setMin(1);
            $step11->setMax(1000);

            $step12 = $this->digitalFormStepFactory->create(DigitalFormStepFactory::TYPE_TEXT_SINGLE);
            $step12->setDefault('Step placeholder ...');
            $step12->setMin(0);
            $step12->setMax(100);

            $step13 = $this->digitalFormStepFactory->create(DigitalFormStepFactory::TYPE_ODOMETER);
            $step13->setDefault(111111);

            $steps = [$step1, $step2, $step3, $step4, $step5, $step6, $step7, $step8, $step9, $step10, $step11, $step12, $step13];
        }

        return $steps[$index % count($steps)];
    }

    private function getStepAnswerValue($key, $index)
    {
        if ($key === 0) {
            $rows = array_merge(
                array_fill(0, 2, '1'),
                array_fill(0, 2, '2'),
                array_fill(0, 6, '1'),
                array_fill(0, 8, '2'),
                array_fill(0, 7, '1'),
                ['130000', 'Lorem text ...'],
                array_fill(0, 2, null)
            );
        } elseif ($key === 2) {
            $rows = [
                55555555,
            ];
        } else {
            $rows = [
                '2020-11-11',
                '2020-11-11 22:22:22',
                null,
                null,
                [1,3],
                22.22,
                1,
                2,
                123,
                null,
                'Lorem text ...',
                'Lorem text ...',
                134444,
            ];
        }

        return $rows[$index % count($rows)];
    }

    private function getFile($key, $index)
    {
        if ($key === 0) {
            $rows = array_merge(
                array_fill(0, 27, null),
                ['file', 'file']
            );
        } else {
            $rows = array_merge(
                array_fill(0, 2, null),
                ['file', 'file'],
                array_fill(0, 5, null),
                ['file'],
                array_fill(0, 2, null)
            );
        }

        // create file entity if need
        if ($rows[$index % count($rows)] !== null) {
            $file = new File('1377a31295756548814ff398f90da874.png', '/srv/web/uploads/digital_form/', $this->admin);
            $file->setDisplayName('test.png');
            $this->manager->persist($file);

            return $file;
        } else {
            return null;
        }
    }
}
