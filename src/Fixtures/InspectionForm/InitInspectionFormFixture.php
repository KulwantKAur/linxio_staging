<?php

namespace App\Fixtures\InspectionForm;

use App\Entity\InspectionForm;
use App\Entity\InspectionFormTemplate;
use App\Entity\InspectionFormVersion;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use App\Fixtures\Permissions\InitPermissionsFixture;
use App\Fixtures\Roles\InitRolesFixture;
use App\Fixtures\Teams\InitTeamsFixture;
use App\Fixtures\Users\InitDemoUsersFixtures;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

class InitInspectionFormFixture extends BaseFixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            InitPermissionsFixture::class,
            InitRolesFixture::class,
            InitDemoUsersFixtures::class,
            InitTeamsFixture::class
        ];
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
        /** @var EntityManager $manager */
        $manager = $this->prepareEntityManager($manager);
        $form = $manager->getRepository(InspectionForm::class)
            ->findOneBy(['title' => 'test inspection form']);
        if (!$form) {
            $form = new InspectionForm(['title' => 'test inspection form']);
            $form->setIsDefault(false);
            $manager->persist($form);
        }

        $version = new InspectionFormVersion(
            [
                'version' => 1,
                'form' => $form
            ]
        );
        $manager->persist($version);

        $checkBox = new InspectionFormTemplate(
            [
                'type' => InspectionFormTemplate::CHECKBOX,
                'title' => 'test checkbox',
                'description' => 'test description text',
                'version' => $version
            ]
        );
        $manager->persist($checkBox);

        $manager->flush();
    }
}
