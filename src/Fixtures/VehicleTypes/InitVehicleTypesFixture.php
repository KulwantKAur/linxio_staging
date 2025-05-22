<?php

namespace App\Fixtures\VehicleTypes;


use App\Entity\VehicleType;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use App\Service\File\LocalFileService;
use App\Service\Vehicle\VehicleServiceHelper;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Exception;
use \Symfony\Component\HttpFoundation\File\UploadedFile;

class InitVehicleTypesFixture extends BaseFixture implements FixtureGroupInterface
{
    private $fileService;
    private $fixturesPath;

    public function __construct(LocalFileService $fileService, $fixturesPath)
    {
        $this->fileService = $fileService;
        $this->fixturesPath = $fixturesPath;
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return [FixturesTypes::GLOBAL];
    }

    private const CAR_IMAGES_PATH = [
        VehicleType::DEFAULT_PICTURE => 'black-car.png',
        VehicleType::DRIVING_PICTURE => 'green-car.png',
        VehicleType::IDLING_PICTURE => 'blue-car.png',
        VehicleType::STOPPED_PICTURE => 'gray-car.png'
    ];

    private const BUS_IMAGES_PATH = [
        VehicleType::DEFAULT_PICTURE => 'black-car.png',
        VehicleType::DRIVING_PICTURE => 'green-car.png',
        VehicleType::IDLING_PICTURE => 'blue-car.png',
        VehicleType::STOPPED_PICTURE => 'gray-car.png'
    ];

    private const TRUCK_IMAGES_PATH = [
        VehicleType::DEFAULT_PICTURE => 'black-car.png',
        VehicleType::DRIVING_PICTURE => 'green-car.png',
        VehicleType::IDLING_PICTURE => 'blue-car.png',
        VehicleType::STOPPED_PICTURE => 'gray-car.png'
    ];


    public const TYPES = [
        [
            'name' => VehicleType::CAR,
            'files' => self::CAR_IMAGES_PATH
        ],
        [
            'name' => VehicleType::BUS,
            'files' => self::BUS_IMAGES_PATH
        ],
        [
            'name' => VehicleType::TRUCK,
            'files' => self::TRUCK_IMAGES_PATH
        ],
        [
            'name' => VehicleType::VAN,
            'files' => self::CAR_IMAGES_PATH
        ],
        [
            'name' => VehicleType::UTILITY_VEHICLE,
            'files' => self::CAR_IMAGES_PATH
        ],
        [
            'name' => VehicleType::SMALL_TRUCK,
            'files' => self::CAR_IMAGES_PATH
        ],
        [
            'name' => VehicleType::LARGE_TRUCK,
            'files' => self::CAR_IMAGES_PATH
        ],
        [
            'name' => VehicleType::REF_TRUCK,
            'files' => self::CAR_IMAGES_PATH
        ],
        [
            'name' => VehicleType::TRAILER,
            'files' => self::CAR_IMAGES_PATH
        ],
        [
            'name' => VehicleType::EXCAVATOR,
            'files' => self::CAR_IMAGES_PATH
        ],
        [
            'name' => VehicleType::FORKLIFT,
            'files' => self::CAR_IMAGES_PATH
        ],
        [
            'name' => VehicleType::BULLDOZER,
            'files' => self::CAR_IMAGES_PATH
        ],
        [
            'name' => VehicleType::CONTAINER,
            'files' => self::CAR_IMAGES_PATH
        ],
        [
            'name' => VehicleType::PERSON,
            'files' => self::CAR_IMAGES_PATH
        ]
    ];

    /**
     * @param ObjectManager $manager
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        /** @var EntityManager $manager */
        $manager = $this->prepareEntityManager($manager);

        foreach (self::TYPES as $TYPE) {
            /** @var VehicleType $newType */
            $newType = $manager->getRepository(VehicleType::class)->getVehiclesTypeByName(strtolower($TYPE['name']));

            if (!$newType) {
                $files = VehicleServiceHelper::prepareVehicleTypePictures(
                    $this->prepareTypeFiles($TYPE), $this->fileService, null
                );
                $newType = new VehicleType(array_merge($TYPE, $files));
                $manager->persist($newType);
            }
        }
        $manager->flush();
    }

    private function prepareTypeFiles($type)
    {
        $images = [];
        foreach ($type['files'] as $key => $fileName) {
            $filePath = $this->fixturesPath . '/VehicleTypes/images/' . $fileName;
            copy($filePath, $this->fixturesPath . '/VehicleTypes/images/_' . $fileName);
            $copyPath = $this->fixturesPath . '/VehicleTypes/images/_' . $fileName;
            $images[$key] = new UploadedFile($copyPath, $fileName, null, UPLOAD_ERR_OK, true);
        }

        return $images;
    }
}
