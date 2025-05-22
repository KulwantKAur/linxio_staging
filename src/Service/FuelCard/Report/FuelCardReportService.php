<?php

namespace App\Service\FuelCard\Report;

use App\Entity\Currency;
use App\Entity\FuelCard\FuelCard;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Report\Core\Formatter\Header\PostfixFormatter;
use App\Service\BaseService;
use App\Service\ElasticSearch\ElasticSearch;
use App\Service\Vehicle\VehicleService;
use Doctrine\ORM\EntityManagerInterface;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Contracts\Translation\TranslatorInterface;

class FuelCardReportService extends BaseService
{
    private $em;
    private $finder;
    private $fuelCardRepo;
    protected $translator;
    protected $vehicleService;

    public const AGGREGATIONS_FIELDS = 'aggregations';

    public const ELASTIC_NESTED_FIELDS = [];

    public const ELASTIC_SIMPLE_FIELDS = [
        'id' => 'id',
        'fuelCardNumber' => 'fuelCardNumber',
        'petrolStation' => 'petrolStation',
        'status' => 'status',
        'vehicleId' => 'vehicle.id',
        'vehicleIds' => 'vehicle.id',
        'vehicleRegNo' => 'vehicle.regno',
        'vehicleDepot' => 'vehicle.depot.id',
        'vehicleModel' => 'vehicle.model',
        'vehicleTeamId' => 'vehicle.team.id',
        'regNo' => 'vehicle.regno',
        'vehicleDefaultLabel' => 'vehicle.defaultLabel',
        'vehicleGroups' => 'vehicle.groups.id',
        'vehicleFuelType' => 'vehicle.fuelType.id',
        'teamId' => 'teamId',
        'driver' => 'driver.fullName',
        'driverId' => 'driver.id',
        'refueledFuelType' => 'refueledFuelType.id',
        'odometer' => 'odometer',
        'pumpPrice' => 'pumpPrice',
        'siteId' => 'siteId',
        'productCode' => 'productCode',
        'vehicleFarFromStation' => 'vehicleFarFromStation'
    ];

    public const ELASTIC_RANGE_FIELDS = [
        'transactionDate' => 'transactionDate',
        'refueled' => 'refueled',
        'total' => 'total',
        'fuelPrice' => 'fuelPrice',
    ];

    /**
     * FuelCardReportService constructor.
     * @param TransformedFinder $finder
     * @param EntityManagerInterface $em
     * @param TranslatorInterface $translator
     * @param VehicleService $vehicleService
     */
    public function __construct(
        TransformedFinder $finder,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        VehicleService $vehicleService
    ) {
        $this->em = $em;
        $this->finder = new ElasticSearch($finder);
        $this->translator = $translator;
        $this->vehicleService = $vehicleService;
        $this->fuelCardRepo = $em->getRepository(FuelCard::class);
    }

    /**
     * @param array $params
     * @param User $user
     * @param bool $paginated
     *
     * @return mixed
     */
    public function getFuelCardReport(array $params, User $user, bool $paginated = true)
    {
        $result = $this->getElasticSearchData($params, $user, $paginated);

        if (isset($result[self::AGGREGATIONS_FIELDS]) && !empty($result[self::AGGREGATIONS_FIELDS])) {
            $result['additionalFields']['total'] = array_intersect_key(
                $result[self::AGGREGATIONS_FIELDS],
                array_flip(FuelCard::DISPLAYED_VALUES)
            );

            foreach ($result['additionalFields']['total'] as $addFKey => $addFValue) {
                $result['additionalFields']['total'][$addFKey] = is_float($addFValue['value'])
                    ? round($addFValue['value'], 2)
                    : $addFValue['value'];
            }
        }

        return $result;
    }

    /**
     * @param array $params
     * @param User $user
     * @param bool $paginated
     *
     * @return array
     */
    public function getFuelCardVehicles(array $params, User $user, bool $paginated = true)
    {
        $vehicleIds = $this->getFuelCardVehicleIds($params, $user, false);
        $params['id'] = $vehicleIds;

        return $this->vehicleService->vehicleList($params, $user, true, Vehicle::REPORT_VALUES);
    }

    public function getFuelCardVehicleIds($params, $user, $paginated)
    {
        $result = $this->getElasticSearchData($params, $user, $paginated);

        if ($paginated) {
            return array_column($result[self::AGGREGATIONS_FIELDS], 'key');
        } else {
            $vehicleIds = array_map(function (FuelCard $fuelCard) {
                return $fuelCard->getVehicle() ? $fuelCard->getVehicle()->getId() : null;
            }, $result);

            return array_unique(array_filter($vehicleIds));
        }
    }

    public function getFuelCardReportByVehicle(array $params, User $user)
    {
        $vehicleIds = $this->getFuelCardVehicleIds($params, $user, false);
        $result = ['vehicles' => []];
        $result['total'] = [];
        unset($params['vehicleIds']);
        foreach ($vehicleIds as $vehicleId) {
            /** @var Vehicle $vehicle */
            $vehicle = $this->em->getRepository(Vehicle::class)->find($vehicleId);
            if (!$vehicle) {
                continue;
            }
            $data = $this->getFuelCardReportExportData(array_merge($params, ['vehicleId' => $vehicleId]), $user);


            $result['vehicles'][] = [
                'vehicle' => $vehicle->toArray(Vehicle::REPORT_VALUES),
                'data' => $data
            ];
        }

        return $result;
    }

    /**
     * @param array $params
     * @param User $user
     * @param bool $paginated
     *
     * @return array|mixed
     */
    public function getElasticSearchData(array $params, User $user, bool $paginated = true)
    {
        if ($user->isInClientTeam()) {
            $params['teamId'] = $user->getTeam()->getId();

            if (!$user->getTeam()->isChevron()) {
                $params['vehicleTeamId'] = $user->getTeam()->getId();
            }
        }

        $params = $this->getPrepareFields($params, $user);
        $fields = $this->prepareElasticFields($params);

        $result = $this->finder->find($fields, $fields['_source'] ?? [], $paginated);
        if ($paginated) {
            $result = $this->getAggregationData($result, $paginated);
        }

        return $result;
    }

    /**
     * @param array $params
     * @param User $user
     *
     * @return array
     */
    public function getPrepareFields(array $params, User $user): array
    {
        $params['status'] = $params['status'] ?? FuelCard::STATUS_ACTIVE;
        if (isset($params['startDate']) && is_object($params['startDate'])) {
            $params['transactionDate']['gt'] = $params['startDate']->format('c');
            $params['transactionDate']['lt'] = $params['endDate']->format('c');
        } else {
            $params['transactionDate']['gt'] = $params['startDate']['gte'] ?? $params['startDate'] ?? null;
            $params['transactionDate']['lt'] = $params['startDate']['lt'] ?? $params['endDate'] ?? null;
        }

        $params['fields'] = array_merge(FuelCard::DISPLAYED_VALUES, $params['fields'] ?? []);
        $params[self::AGGREGATIONS_FIELDS] = array_merge(
            FuelCard::DEFAULT_AGGREGATIONS_FIELDS,
            $params[self::AGGREGATIONS_FIELDS] ?? []
        );

        if (isset($params['sort']) && str_contains($params['sort'], 'startDate')) {
            $prefix = !str_contains($params['sort'], '-') ? '' : '-';
            $params['sort'] = $prefix . 'transactionDate';
        }

        /** @var User $user */
        if ($user->isInClientTeam() || $user->isInResellerTeam()) {
            $params['teamId'] = $user->getTeam()->getId();
        }

        if (isset($params['vehicleIds']) && is_array($params['vehicleIds']) && count($params['vehicleIds']) > 1 && in_array('null',
                $params['vehicleIds'], true)) {
            $params[self::ELASTIC_CASE_OR]['vehicle.id'][] = array_diff($params['vehicleIds'], ['null']);
            $params[self::ELASTIC_CASE_OR]['vehicle.id'][] = null;
            unset($params['vehicleIds']);
        } elseif (isset($params['vehicleIds']) && is_array($params['vehicleIds']) && in_array('null',
                $params['vehicleIds'], true)) {
            $params['vehicleIds'] = null;
        }

        return $params;
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    public function getAggregationData($data, $paginated)
    {
        if ($paginated) {
            $dataAggs = $data[self::AGGREGATIONS_FIELDS];
            foreach ($dataAggs as $key => $value) {
                if (isset($dataAggs[$key]['hits'])) {
                    $dataAggs[$key]['top_hits'] = $value['hits']['hits'][0]['_source'] ?? null;
                }
            }
            $data[self::AGGREGATIONS_FIELDS] = $dataAggs;
        }

        return $data;
    }

    public function prepareExportData($params, User $user)
    {
        $fuelCards = $this->getFuelCardReport($params, $user, false);
        $currencyCode = $user->getTeam()->getPlatformSettingByTeam()?->getCurrency()?->getCode() ?? Currency::AUD;

        $postfixFormatter = new PostfixFormatter([
            'fuelcard.total' => ', ' . strtoupper($currencyCode),
            'fuelcard.fuelPrice' => ', ' . strtoupper($currencyCode) . '/L',
            'fuelcard.pumpPrice' => ', ' . strtoupper($currencyCode) . '/L',
        ]);

        if ($user->getTeam()->isChevron() && array_search('odometer', $params['fields']) !== false) {
            $params['fields'][array_search('odometer', $params['fields'])] = 'ifcsOdometer';
        }

        if ($user->getTeam()->isChevron() && array_search('petrolStation', $params['fields']) !== false) {
            $params['fields'][array_search('petrolStation', $params['fields'])] = 'serviceStation';
        }

        return $this->translateEntityArrayForExport($fuelCards, $params['fields'], null, $user, $postfixFormatter);
    }

    public function getFuelCardReportExportData($params, User $user)
    {
        return $this->prepareExportData($params, $user);
    }
}
