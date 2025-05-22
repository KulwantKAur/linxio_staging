<?php

namespace App\Entity\FuelCard;

use App\Entity\BaseEntity;
use App\Entity\Device;
use App\Entity\File;
use App\Entity\FuelStation;
use App\Entity\FuelType\FuelType;
use App\Entity\Setting;
use App\Entity\Team;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Service\MapService\MapServiceInterface;
use App\Util\GeoHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;

/**
 * FuelCard
 */
#[ORM\Table(name: 'fuel_card')]
#[ORM\Index(name: 'fuel_card_team_id_index', columns: ['team_id'])]
#[ORM\Entity(repositoryClass: 'App\Repository\FuelCard\FuelCardRepository')]
#[ORM\EntityListeners(['App\EventListener\FuelCard\FuelCardEntityListener'])]
class FuelCard extends BaseEntity
{
    public const DISPLAYED_VALUES = [
        'id',
        'fuelType',
        'fuelCardNumber',
        'refueled',
        'total',
        'fuelPrice',
        'petrolStation',
        'refueledFuelType',
        'transactionDate',
        'isShowTime',
        'vehicle',
        'driver',
        'status',
        'teamId',
        'vehicleTeamId',
        'odometer',
        'cardAccountNumber',
        'pumpPrice',
        'productCode',
        'siteId',
        'updatedAt',
        'updatedBy',
    ];

    public const DISPLAYED_VALUES_TEMPORARY = [
        'id',
        'vehicle',
        'transactionDate',
        'isShowTime',
        'refueledFuelType',
        'refueled',
        'comments',
    ];

    public const DISPLAYED_FUEL_SUMMARY = [
        'vehicle',
        'refueled',
        'total',
        'fuelPrice',
        'refueledFuelType',
        'transactionDate',
        'status',
    ];

    public const EXPORT_FUEL_SUMMARY = [
        'reg_no',
        'depot',
        'groups',
        'mileage',
        'refueled',
        'total',
    ];

    public const EXPORT_FUEL_RECORDS = [
        'transactionDate',
        'regNo',
        'defaultLabel',
        'model',
        'driver',
        'depotName',
        'groupsList',
        'fuelType',
        'fuelCardNumber',
        'refueled',
        'total',
        'fuelPrice',
        'petrolStation',
        'productCode',
        'siteId'
    ];

    public const DEFAULT_AGGREGATIONS_FIELDS = [
        'terms' => [
            'vehicle.id'
        ],
        'aggs' => [
            'sum' => [
                'refueled',
                'total'
            ],
            'top_hits' => [
                'vehicle.regno',
                'vehicle.depot.name',
                'vehicle.groups.name',
            ],
        ]
    ];

    public const STATUS_ACTIVE = 'saved';
    public const STATUS_DRAFT = 'draft';

    private const FAR_LIMIT = 500;
    private const STATUS_UNDETECTABLE = 'undetectable';
    private const UNDETECTABLE_VALUE = 900;

    /**
     * FuelCard constructor.
     * @param array $fields
     * @throws \Exception
     */
    public function __construct(array $fields)
    {
        $this->setFuelCardNumber($fields['fuelCardNumber'] ?? null);
        $this->setFuelPrice($fields['fuelPrice'] ?? null);
        $this->setRefueled($fields['refueled'] ?? null);
        $this->setRefueledFuelType($fields['refueledFuelType'] ?? null);
        $this->setTransactionDate($fields['transactionDate'] ?? null);
        $this->setPetrolStation($fields['petrolStation'] ?? null);
        $this->setTotal($fields['total'] ?? null);
        $this->setVehicle($fields['vehicle'] ?? null);
        $this->setFile($fields['file'] ?? null);
        $this->setIsShowTime($fields['isShowTime']);
        $this->setDriver($fields['driver'] ?? null);
        $this->setFuelCardTemporary($fields['fuelCardTemporary'] ?? null);
        $this->setCreatedAt(new \DateTime());
        $this->setTeamId($fields['teamId'] ?? null);
        $this->setOdometer($fields['odometer'] ?? null);
        $this->setCardAccountNumber($fields['cardAccountNumber'] ?? null);
        $this->setSiteId($fields['siteId'] ?? null);
        $this->setProductCode($fields['productCode'] ?? null);
        $this->setPumpPrice($fields['pumpPrice'] ?? null);
    }

    /**
     * @param array $include
     * @return array
     * @throws \Exception
     */
    public function toArray(array $include = []): array
    {
        $data = [];

        if (empty($include)) {
            $include = self::DISPLAYED_VALUES;
        }

        if (in_array('id', $include, true)) {
            $data['id'] = $this->getId();
        }

        if (in_array('fuelCardNumber', $include, true)) {
            $data['fuelCardNumber'] = $this->getFuelCardNumber();
        }

        if (in_array('refueled', $include, true)) {
            $data['refueled'] = $this->getRefueled();
        }

        if (in_array('total', $include, true)) {
            $data['total'] = $this->getTotal();
        }

        if (in_array('fuelPrice', $include, true)) {
            $data['fuelPrice'] = $this->getFuelPrice();
        }

        if (in_array('petrolStation', $include, true)) {
            $data['petrolStation'] = $this->getPetrolStation();
        }

        if (in_array('refueledFuelType', $include, true)) {
            $data['refueledFuelType'] = $this->getRefueledFuelTypeArray();
        }

        if (in_array('transactionDate', $include, true)) {
            $data['transactionDate'] = $this->getTransactionDate();
        }

        if (in_array('isShowTime', $include, true)) {
            $data['isShowTime'] = $this->isShowTime();
        }

        if (in_array('vehicle', $include, true)) {
            $data['vehicle'] = $this->getVehicleArray();
        }

        if (in_array('driver', $include, true)) {
            $data['driver'] = $this->getDriver()?->getFullName();
        }

        if (in_array('file', $include, true)) {
            $data['file'] = $this->getFile()?->toArray();
        }

        if (in_array('teamId', $include, true)) {
            $data['teamId'] = $this->getFile()?->getTeamId();
        }

        if (in_array('vehicleTeamId', $include, true)) {
            $data['vehicleTeamId'] = $this->getVehicleArray()['team'] ?? null;
        }

        if (in_array('comments', $include, true)) {
            $data['comments'] = $this->getComments();
        }

        if (in_array('status', $include, true)) {
            $data['status'] = $this->getStatus();
        }

        if (in_array('carCoordinates', $include, true)) {
            $data['carCoordinates'] = $this->getVehicleCoordinates();
        }

        if (in_array('teamId', $include, true)) {
            $data['teamId'] = $this->getTeamId();
        }

        if (in_array('odometer', $include, true)) {
            $data['odometer'] = $this->getOdometer();
        }

        if (in_array('cardAccountNumber', $include, true)) {
            $data['cardAccountNumber'] = $this->getCardAccountNumber();
        }

        if (in_array('siteId', $include, true)) {
            $data['siteId'] = $this->getSiteId();
        }

        if (in_array('productCode', $include, true)) {
            $data['productCode'] = $this->getProductCode();
        }

        if (in_array('pumpPrice', $include, true)) {
            $data['pumpPrice'] = $this->getPumpPrice();
        }

        if (in_array('stationAddress', $include, true)) {
            $data['stationAddress'] = $this->getStationAddress();
        }

        if (in_array('vehicleAddress', $include, true)) {
            $data['vehicleAddress'] = $this->getVehicleAddress();
        }

        if (in_array('vehicleFarFromStation', $include, true)) {
            $data['vehicleFarFromStation'] = $this->isVehicleFarFromStation();
        }

        if (in_array('updatedAt', $include, true)) {
            $data['updatedAt'] = $this->formatDate($this->getUpdatedAt());
        }

        if (in_array('updatedBy', $include, true)) {
            $data['updatedBy'] = $this->getUpdatedBy()?->toArray(User::CREATED_BY_FIELDS);
        }

        return $data;
    }

    /**
     * @param array $include
     * @return array
     * @throws \Exception
     */
    public function toExport(array $include = [], ?User $user = null): array
    {
        $currency = $user?->getTeam()->getPlatformSettingByTeam()?->getCurrency();

        $data = $this->toArray($include);

        if (in_array('startDate', $include, true)) {
            $data['startDate'] = $this->formatDate(
                $this->getTransactionDate(),
                $user?->getDateFormatSettingConverted($this->isShowTime()),
                $user?->getTimezone()
            );
        }

        if (in_array('regNo', $include, true)) {
            $data['regNo'] = isset($this->getVehicleArray()['regNo'])
                ? ($this->getUpdatedAt() ? '*' . $this->getVehicleArray()['regNo'] : $this->getVehicleArray()['regNo'])
                : null;
        }

        if (in_array('model', $include, true)) {
            $data['model'] = $this->getVehicleArray()['model'] ?? null;
        }

        if (in_array('defaultLabel', $include, true)) {
            $data['defaultLabel'] = $this->getVehicleArray()['defaultLabel'] ?? null;
        }

        if (in_array('fuelType', $include, true)) {
            $data['fuelType'] = isset($this->getRefueledFuelTypeArray()['name']) && $this->getRefueledFuelTypeArray()['name']
                ? $this->getRefueledFuelTypeArray()['name']
                : null;
        }

        if (in_array('groupsList', $include, true)) {
            $data['groupsList'] = $this->getVehicleArray()['groupsList'] ?? null;
        }

        if (in_array('depotName', $include, true)) {
            $data['depotName'] = $this->getVehicleArray()['depotName'] ?? null;
        }

        if (in_array('refueledFuelType', $include, true)) {
            $data['refueledFuelType'] = isset($this->getRefueledFuelTypeArray()['name']) && $this->getRefueledFuelTypeArray()['name']
                ? $this->getRefueledFuelTypeArray()['name']
                : null;
        }

        if (in_array('transactionDate', $include, true)) {
            if ($user) {
                $data['transactionDate'] = $this->formatDate(
                    $this->getTransactionDate(),
                    self::EXPORT_DATE_FORMAT,
                    $user->getTimezone()
                );
            } else {
                $data['transactionDate'] = $this->formatDate($this->getTransactionDate(), self::EXPORT_DATE_FORMAT);
            }
        }

        if (in_array('total', $include, true)) {
            $data['total'] = number_format($this->getTotal(), $currency?->getDecimals() ?? 2);
        }

        if (in_array('fuelPrice', $include, true)) {
            $data['fuelPrice'] = number_format($this->getFuelPrice(), $currency?->getDecimals() ?? 2);
        }

        if (in_array('pumpPrice', $include, true)) {
            $data['pumpPrice'] = number_format($this->getPumpPrice(), $currency?->getDecimals() ?? 2);
        }

        if (in_array('ifcsOdometer', $include, true)) {
            $data['ifcsOdometer'] = $this->getOdometer();
        }

        if (in_array('serviceStation', $include, true)) {
            $data['serviceStation'] = $this->getPetrolStation();
        }

        return array_merge(array_flip($include ?? self::EXPORT_FUEL_RECORDS), $data);
    }

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var Vehicle
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Vehicle')]
    #[ORM\JoinColumn(name: 'vehicle_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $vehicle;

    /**
     * @var File
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\File')]
    #[ORM\JoinColumn(name: 'file_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $file;

    /**
     * @var string
     */
    #[ORM\Column(name: 'fuel_card_number', type: 'string', nullable: true)]
    private $fuelCardNumber;

    /**
     * @var int
     */
    #[ORM\Column(name: 'refueled', type: 'float', nullable: true)]
    private $refueled;

    /**
     * @var int
     */
    #[ORM\Column(name: 'total', type: 'float', nullable: true)]
    private $total;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'fuel_price', type: 'float', nullable: true)]
    private $fuelPrice;

    /**
     * @var string
     */
    #[ORM\Column(name: 'petrol_station', type: 'string', length: 255, nullable: true)]
    private $petrolStation;

    /**
     * @var FuelType
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\FuelType\FuelType')]
    #[ORM\JoinColumn(name: 'refueled_fuel_type_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $refueledFuelType;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'transaction_date', type: 'datetime', nullable: true)]
    private $transactionDate;

    /**
     * @var boolean
     */
    #[ORM\Column(name: 'is_show_time', type: 'boolean', nullable: false, options: ['default' => true])]
    private $isShowTime = true;

    /**
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\User')]
    #[ORM\JoinColumn(name: 'driver_id', referencedColumnName: 'id', nullable: true)]
    private $driver;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: true)]
    private $createdAt;

    /**
     * @var string
     */
    #[ORM\Column(name: 'status', type: 'string', length: 255)]
    private $status = self::STATUS_DRAFT;

    #[ORM\Column(name: 'vehicle_coordinates', type: 'geometry', nullable: true, options: ['geometry_type' => 'POINT'])]
    private $vehicleCoordinates;

    #[ORM\Column(name: 'petrol_station_coordinates', type: 'geometry', nullable: true, options: ['geometry_type' => 'POINT'])]
    private $petrolStationCoordinates;

    /**
     * @var FuelCardTemporary|null
     */
    #[ORM\OneToOne(targetEntity: 'App\Entity\FuelCard\FuelCardTemporary')]
    #[ORM\JoinColumn(name: 'fuel_card_temporary_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private $fuelCardTemporary;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'team_id', type: 'integer', nullable: true)]
    private ?int $teamId;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'odometer', type: 'bigint', nullable: true)]
    private ?int $odometer;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'card_account_number', type: 'bigint', nullable: true)]
    private ?int $cardAccountNumber;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'site_id', type: 'bigint', nullable: true)]
    private ?int $siteId;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'product_code', type: 'integer', nullable: true)]
    private ?int $productCode;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'pump_price', type: 'float', nullable: true)]
    private ?float $pumpPrice;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private $updatedAt;

    /**
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\User')]
    #[ORM\JoinColumn(name: 'updated_by', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $updatedBy;

    private EntityManager $em;
    private MapServiceInterface $mapService;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Vehicle|null $vehicle
     * @return $this
     */
    public function setVehicle(?Vehicle $vehicle)
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    /**
     * @return Vehicle|null
     */
    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    /**
     * @return array|string
     * @throws \Exception
     */
    public function getVehicleArray()
    {
        return $this->vehicle
            ? $this->vehicle->toArray(
                [
                    'regNo',
                    'model',
                    'defaultLabel',
                    'fuelTypeArray',
                    'groups',
                    'groupsList',
                    'depot',
                    'depotName',
//                    'driver',
                    'team',
                ]
            )
            : $this->getFuelCardTemporary()->getVehicleOriginal();
    }

    /**
     * Set fuelCardNumber.
     *
     * @param string $fuelCardNumber
     *
     * @return FuelCard
     */
    public function setFuelCardNumber($fuelCardNumber)
    {
        $this->fuelCardNumber = $fuelCardNumber;

        return $this;
    }

    /**
     * Get fuelCardNumber.
     *
     * @return string
     */
    public function getFuelCardNumber()
    {
        return $this->fuelCardNumber;
    }

    /**
     * Set refueled.
     *
     * @param int $refueled
     *
     * @return FuelCard
     */
    public function setRefueled($refueled)
    {
        $this->refueled = $refueled;

        return $this;
    }

    /**
     * Get refueled.
     *
     * @return int
     */
    public function getRefueled()
    {
        return $this->refueled;
    }

    /**
     * Set total.
     *
     * @param int $total
     *
     * @return FuelCard
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Get total.
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set fuelPrice.
     *
     * @param int|null $fuelPrice
     *
     * @return FuelCard
     */
    public function setFuelPrice($fuelPrice)
    {
        $this->fuelPrice = $fuelPrice;

        return $this;
    }

    /**
     * Get fuelPrice.
     *
     * @return int|null
     */
    public function getFuelPrice()
    {
        return $this->fuelPrice;
    }

    /**
     * Set petrolStation.
     *
     * @param string $petrolStation
     *
     * @return FuelCard
     */
    public function setPetrolStation($petrolStation)
    {
        $this->petrolStation = $petrolStation;

        return $this;
    }

    /**
     * Get petrolStation.
     *
     * @return string
     */
    public function getPetrolStation()
    {
        if ($this->getTeam()?->isChevron()) {
            return $this->em->getRepository(FuelStation::class)
                ->findOneBy(['siteId' => $this->getSiteId()])?->getStationName() ?? $this->petrolStation;
        }

        return $this->petrolStation;
    }

    /**
     * @param FuelType|null $refueledFuelType
     *
     * @return $this
     */
    public function setRefueledFuelType(?FuelType $refueledFuelType)
    {
        $this->refueledFuelType = $refueledFuelType;

        return $this;
    }

    /**
     * @return FuelType|null
     */
    public function getRefueledFuelType(): ?FuelType
    {
        return $this->refueledFuelType ?: null;
    }

    /**
     * @return array|string
     */
    public function getRefueledFuelTypeArray()
    {
        if ($this->getVehicle()?->getTeam()->isChevron() || $this->getTeam()?->isChevron()) {
            if ($this->refueledFuelType) {
                $fuelType = $this->refueledFuelType->toArray(['id', 'name']);
                $fuelType['name'] = FuelType::convertFuelTypeForChevron($fuelType['name']);
            } else {
                $fuelType = $this->getFuelCardTemporary()->getRefueledFuelTypeOriginal();
            }

            return $fuelType;
        }

        return $this->refueledFuelType
            ? $this->refueledFuelType->toArray(['id', 'name'])
            : $this->getFuelCardTemporary()->getRefueledFuelTypeOriginal();
    }

    /**
     * @param $transactionDate
     * @return $this
     */
    public function setTransactionDate($transactionDate)
    {
        $this->transactionDate = $transactionDate;

        return $this;
    }

    /**
     * @return string|null
     * @throws \Exception
     */
    public function getTransactionDate()
    {
        return $this->transactionDate
            ? $this->formatDate($this->transactionDate)
            : null;
    }

    /**
     * @param \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set file.
     *
     * @param $file
     *
     * @return $this
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get file.
     * @return array
     * @throws \Exception
     */
    public function getFile(): File
    {
        return $this->file;
    }

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus(?string $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return bool
     */
    public function isShowTime(): bool
    {
        return $this->isShowTime;
    }

    /**
     * @param bool $isShowTime
     */
    public function setIsShowTime(bool $isShowTime): void
    {
        $this->isShowTime = $isShowTime;
    }

    /**
     * @param User|null $driver
     * @return $this
     */
    public function setDriver(?User $driver)
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * Get driver.
     *
     * @return User
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @return mixed
     */
    public function getVehicleCoordinates()
    {
        return $this->vehicleCoordinates;
    }

    /**
     * @param array|null $vehicleCoordinates
     * @return FuelCard
     */
    public function setVehicleCoordinates(?array $vehicleCoordinates): self
    {
        $this->vehicleCoordinates = $this->convertCoordinatesToPoint($vehicleCoordinates);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPetrolStationCoordinates()
    {
        return $this->petrolStationCoordinates;
    }

    /**
     * @param array|null $petrolStationCoordinates
     * @return FuelCard
     */
    public function setPetrolStationCoordinates(?array $petrolStationCoordinates): self
    {
        $this->petrolStationCoordinates = $this->convertCoordinatesToPoint($petrolStationCoordinates);

        return $this;
    }

    /**
     * Set fuelCardTemporary.
     *
     * @param \App\Entity\FuelCard\FuelCardTemporary|null $fuelCardTemporary
     * @return $this
     */
    public function setFuelCardTemporary(?FuelCardTemporary $fuelCardTemporary)
    {
        $this->fuelCardTemporary = $fuelCardTemporary;

        return $this;
    }

    /**
     * Get fuelCardTemporary.
     *
     * @return \App\Entity\FuelCard\FuelCardTemporary
     */
    public function getFuelCardTemporary(): ?FuelCardTemporary
    {
        return $this->fuelCardTemporary;
    }

    /**
     * @param int|null $teamId
     *
     * @return FuelCard
     */
    public function setTeamId(?int $teamId): self
    {
        $this->teamId = $teamId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getTeamId(): ?int
    {
        return $this->teamId;
    }

    /**
     * @param int|null $odometer
     *
     * @return FuelCard
     */
    public function setOdometer(?int $odometer): self
    {
        $this->odometer = $odometer;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getOdometer(): ?int
    {
        return $this->odometer;
    }

    /**
     * @param int|null $cardAccountNumber
     *
     * @return FuelCard
     */
    public function setCardAccountNumber(?int $cardAccountNumber): self
    {
        $this->cardAccountNumber = $cardAccountNumber;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCardAccountNumber(): ?int
    {
        return $this->cardAccountNumber;
    }

    /**
     * @param int|null $siteId
     *
     * @return FuelCard
     */
    public function setSiteId(?int $siteId): self
    {
        $this->siteId = $siteId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getSiteId(): ?int
    {
        return $this->siteId;
    }

    /**
     * @param int|null $productCode
     *
     * @return FuelCard
     */
    public function setProductCode(?int $productCode): self
    {
        $this->productCode = $productCode;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getProductCode(): ?int
    {
        return $this->productCode;
    }

    /**
     * @param float|null $pumpPrice
     *
     * @return FuelCard
     */
    public function setPumpPrice(?float $pumpPrice): self
    {
        $this->pumpPrice = $pumpPrice;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPumpPrice(): ?float
    {
        return $this->pumpPrice ? $this->pumpPrice / 100 : null;
    }

    /**
     * @return array
     */
    public function getComments()
    {
        return $this->getFuelCardTemporary()->getComments();
    }

    /**
     * @return |null
     */
    public function getMileage()
    {
        return null;
    }

    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }

    public function setMapService(MapServiceInterface $mapService)
    {
        $this->mapService = $mapService;
    }

    public function getStation(): ?FuelStation
    {
        return $this->em->getRepository(FuelStation::class)
            ->findOneBy(['siteId' => $this->getSiteId()]);
    }

    public function getStationAddress(): ?string
    {
        return $this->em->getRepository(FuelStation::class)
            ->findOneBy(['siteId' => $this->getSiteId()])?->getAddress();
    }

    public function getLastTh(): ?TrackerHistory
    {
        if (!$this->getVehicle()) {
            return null;
        }

        $prevTh = $this->em->getRepository(TrackerHistory::class)
            ->getLastTrackerRecordByVehicleAndDate($this->getVehicle(), $this->getTransactionDate());

        $nextTh = $this->em->getRepository(TrackerHistory::class)
            ->getNextTrackerRecordByVehicleAndDate($this->getVehicle(), $this->getTransactionDate());

        if (!$prevTh || !$nextTh || !$this->transactionDate) {
            return $prevTh ?? $nextTh ?? null;
        }
        $prevInterval = (clone $this->transactionDate)->diff($prevTh->getTs(), true);
        $nextInterval = $nextTh->getTs()->diff($this->transactionDate, true);
        $prevDiffDate = (new \DateTimeImmutable())->add($prevInterval);
        $nextDiffDate = (new \DateTimeImmutable())->add($nextInterval);

        return $prevDiffDate < $nextDiffDate ? $prevTh : $nextTh;
    }

    public function getVehicleAddress(): ?string
    {
        $th = $this->getLastTh();

        if ($th) {
            $tsDiff = $this->transactionDate ? abs($this->transactionDate->getTimestamp() - $th->getTs()->getTimestamp()) : null;
            //900 - LIN-3221 (chevron value)
            if ($tsDiff && $tsDiff > self::UNDETECTABLE_VALUE && $tsDiff < $this->getVehicleOfflineSettingValue()) {
                return self::STATUS_UNDETECTABLE;
            } elseif ($tsDiff && $tsDiff > $this->getVehicleOfflineSettingValue()) {
                return Device::STATUS_OFFLINE;
            } elseif ($th->getMovement() && $th->getIgnition()) {
                return Device::STATUS_DRIVING;
            } else {
                return $this->mapService->getLocationByCoordinates($th->getLat(), $th->getLng());
            }
        } else {
            return Device::STATUS_OFFLINE;
        }
    }

    public function isVehicleFarFromStation(): ?bool
    {
        $th = $this->getLastTh();
        $station = $this->getStation();

        if (!$th) {
            return true;
        } else {
            $tsDiff = $this->transactionDate ? abs($this->transactionDate->getTimestamp() - $th->getTs()->getTimestamp()) : null;
            if ($tsDiff && ($tsDiff > self::UNDETECTABLE_VALUE || $tsDiff > $this->getVehicleOfflineSettingValue())) {
                return true;
            }
        }

        if ($station && $th->getLat() && $th->getLng()) {
            $distance = GeoHelper::distanceBetweenTwoCoordinates(
                $th->getLat(), $th->getLng(), $station->getLat(), $station->getLng()
            );

            return $distance > self::FAR_LIMIT;
        }

        return null;
    }

    public function setUpdatedAt($updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedBy(User $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function getTeam(): ?Team
    {
        if ($this->getTeamId()) {
            return $this->em->getRepository(Team::class)->find($this->getTeamId());
        }

        return null;
    }

    private function getVehicleOfflineSettingValue(): ?int
    {
        $gpsStatusDurationSetting = $this->getTeam()?->getSettingsByName(Setting::GPS_STATUS_DURATION)?->getValue();
        if (isset($gpsStatusDurationSetting['enable']) && $gpsStatusDurationSetting['enable']
            && isset($gpsStatusDurationSetting['value']) && $gpsStatusDurationSetting['value']) {
            return (int)$gpsStatusDurationSetting['value'];

        } else {
            return 3600;
        }
    }
}
