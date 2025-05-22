<?php

namespace App\Service\ElasticSearch;

use App\Entity\User;
use App\Util\ClassHelper;
use Elasticsearch\Common\Exceptions\RuntimeException;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ElasticSearch
{
    private $finder;
    public const RANGE_PARAMS = ['gte', 'gt', 'lt', 'lte'];
    public const MAX_PAGE_LIMIT = 9900;
    public const MATCH_PHRASE_PREFIX = 'match_phrase_prefix';
    public const MATCH_BOOL_PREFIX = 'match_bool_prefix';
    public const TERM = 'term';
    public const TERMS = 'terms';

    /**
     * ElasticSearch constructor.
     * @param TransformedFinder $postFinder
     */
    public function __construct(TransformedFinder $postFinder)
    {
        $this->finder = $postFinder;
    }

    /**
     * @param $key
     * @param $value
     * @return string
     */
    protected function getSearchMethod($key, $value)
    {
        switch ($key) {
            case 'created_at':
            case 'last_logged_at':
            case 'lastActiveTime':
            case 'install_date':
            case 'formattedDate':
            case 'defaultLabel':
            case 'defaultlabel':
            case 'name':
            case 'fullname':
            case 'phone':
            case 'email':
            case 'device.imei':
            case 'vehicle.regno':
            case 'vin':
            case 'regNo':
            case 'sensorId':
            case 'sensor.sensorId':
            case 'label':
            case 'sensor.label':
            case 'idNumber':
            case 'manufacturer':
            case 'model':
            case 'serialNumber':
            case 'category':
            case 'location':
            case 'device.vehicle.regNo':
            case 'sensorBleId':
            case 'sensorLabel':
            case 'eventSource':
            case 'vehicle.defaultLabel':
            case 'lastDataReceivedAt':
            case 'addedToTeam':
            case 'deactivatedAt':
            case 'client.name':
            case 'contractId':
            case 'repairVehicle.regNo':
            case 'odometer':
            case 'pumpPrice':
            case 'siteId':
            case 'productCode':
            case 'previousPhones':
            case 'deviceImei':
            case 'waed':
            case 'reseller.companyName':
            case 'team.chevronAccountId':
            case 'driverId':
            case 'driverFOBId':
                return self::MATCH_PHRASE_PREFIX;
            case 'imei':
            case 'imsi':
            case 'iccid':
                return self::MATCH_BOOL_PREFIX;
            case 'id':
            case 'teamId':
            case 'team.id':
            case 'team.type':
            case 'digitalForm.type':
            case 'alertSetting.team':
            case 'client.id':
            case 'groups.id':
            case 'depot.id':
            case 'vehicle.id':
            case 'serviceRecordVehicle.id':
            case 'driver.id':
            case 'driverId':
            case 'status':
            case 'processedStatus':
            case 'statusExt':
            case 'commonDocumentStatus':
            case 'documentType':
            case 'reminderId':
            case 'reminders.id':
            case 'isInArea':
            case 'isInAssetList':
            case 'vehicle.depot.id':
            case 'vehicle.groups.id':
            case 'remindersCategories.id':
            case 'hasReminders':
            case 'digitalForm.active':
            case 'digitalForm.status':
            case 'digitalForm.team.id':
            case 'user.team.id':
            case 'type.id':
            case 'type':
            case 'digitalForm.inspectionPeriod':
            case 'typeName':
            case 'sensor.id':
            case 'isDeleted':
            case 'typeId':
            case 'isPass':
            case 'usage':
            case 'isDeactivated':
            case 'isUnavailable':
            case 'isWithVehicle':
            case 'contractMonths':
            case 'userIds':
            case 'users.id':
            case 'createdBy.teamId':
            case 'chat.id':
            case 'importance':
            case 'intervalType':
            case 'clientId':
            case 'timeFromTo':
            case 'vehicle.driverId':
            case 'systemStatus':
            case 'isDualAccount':
            case 'isInDriverList':
            case 'vehicleFarFromStation':
            case 'plan.name':
            case 'fullSearchField':
            case 'ownership':
                return self::TERMS;
        }

        return is_numeric($value) ? self::TERM : self::MATCH_PHRASE_PREFIX;
    }

    public function find(
        array $params,
        array $entityFields = [],
        bool $paginated = true,
        ?User $user = null,
        bool $iterator = false
    ) {
        $page = $params['page'];
        $limit = $params['limit'];
        $fields = $params['fields'] ?? null;
        $nested = $params['nested'] ?? null;
        $rangeFields = $params['range'] ?? null;
        $fullSearch = $params['fullSearch'] ?? null;
        $aggregations = $params['aggregations'] ?? null;
        $script = $params['script'] ?? null;
        $caseOr = $params['caseOr'] ?? null;

        $page = $page > 0 ? $page : 1;
        $query = null;

        if (!$fields && !$nested && !$rangeFields && !$fullSearch && !$aggregations && !$script) {
            $query = $this->getListByQuery($params['sort']);
        } elseif ($fields || $nested || $rangeFields || $fullSearch || $aggregations) {
            $query = $this->getSearchByQuery(
                $params['sort'],
                $fields,
                $nested,
                $rangeFields,
                $fullSearch,
                $aggregations,
                $script,
                $caseOr
            );
        }
        if (!$query) {
            throw new BadRequestHttpException('Invalid request.');
        }

        if ($iterator) {
            return $this->finder
                ->findPaginated($query)
                ->setNormalizeOutOfRangePages(true)
                ->setMaxPerPage(self::MAX_PAGE_LIMIT)
                ->setCurrentPage(1)
                ->getIterator();
        }

        if (!$paginated) {
            return $this->getAllRecords($query);
        }
        $paginator = $this->finder
            ->findPaginated($query)
            ->setNormalizeOutOfRangePages(true)
            ->setMaxPerPage($limit)
            ->setCurrentPage($page);

        $currentPageResults = $paginator->getCurrentPageResults();

        return [
            'page' => $paginator->getCurrentPage(),
            'limit' => $paginator->getMaxPerPage(),
            'total' => $paginator->getNbResults(),
            'data' => array_map(
                function ($entity) use ($entityFields, $user) {
                    if ($user) {
                        return $entity->toArray($entityFields, $user);
                    } else {
                        return $entity->toArray($entityFields);
                    }
                },
                $currentPageResults
            ),
            'aggregations' => !empty($aggregations)
                ? $paginator->getAdapter()->getAggregations()
                : null
        ];
    }

    /**
     * @param $query
     * @return array
     */
    private function getAllRecords($query)
    {
        $data = [];
        $paginator = $this->finder
            ->findPaginated($query)
            ->setNormalizeOutOfRangePages(true)
            ->setMaxPerPage(self::MAX_PAGE_LIMIT)
            ->setCurrentPage(1);

        for ($i = 1; $i <= $paginator->getNbPages(); $i++) {
            $data = array_merge($data, $paginator->getCurrentPageResults());
            if ($i !== $paginator->getNbPages()) {
                $paginator->setCurrentPage($paginator->getNextPage());
            }
        }

        return $data;
    }

    /**
     * @param null $sort
     * @return array
     */
    private function getListByQuery($sort = null)
    {
        $query = [];

        $query['query']['bool']['must'][]['match_all'] = (object)[];
        $query['sort'] = $this->getSort($sort);

        return $query;
    }

    /**
     * @param $sort
     * @param $searchFields
     * @param $nested
     * @param $rangeFields
     * @param $fullSearch
     * @param $aggregations
     * @param $script
     * @param $caseOr
     * @return array
     */
    private function getSearchByQuery(
        $sort,
        $searchFields,
        $nested,
        $rangeFields,
        $fullSearch,
        $aggregations,
        $script,
        $caseOr
    ) {
        $query = ["track_total_hits" => true];
        if ($rangeFields && count($rangeFields)) {
            foreach ($rangeFields as $key => $value) {
                foreach ($value as $param => $paramValue) {
                    if (in_array($param, self::RANGE_PARAMS)) {
                        $query['query']['bool']['must'][] = ['range' => [$key => [$param => $paramValue]]];
                    }
                }
            }
        }

        if ($fullSearch && count($fullSearch)) {
            $query['query']['bool']['must'][] = [
                'multi_match' => [
                    'query' => $fullSearch['value'],
                    'fields' => $fullSearch['key'],
                    'type' => 'phrase_prefix',
                    'operator' => 'OR'
                ]
            ];
        }

        if ($aggregations && count($aggregations)) {
            $query = $this->getStatsByAggregations($query, $aggregations);
        }

        if ($caseOr && count($caseOr)) {
            foreach ($caseOr as $key => $value) {
                $data = null;
                if (is_array($value)) {
                    foreach ($value as $item) {
                        if (is_null($item)) {
                            $data['bool']['should'][] = ['bool' => ['must_not' => ['exists' => ['field' => $key]]]];
                        } else {
                            $method = $this->getSearchMethod($key, $item);
                            $case = $this->getSearchCase($key, $item);
                            $data['bool']['should'][] = [$method => $case];
                        }
                    }
                    $query['query']['bool']['should'][] = $data;
                } else {
                    $method = $this->getSearchMethod($key, $value);
                    $case = $this->getSearchCase($key, $value);
                    $query['query']['bool']['should'][] = [$method => $case];
                }
            }
            $query['query']['bool']['minimum_should_match'] = 1;
        }

        if ($searchFields && count($searchFields)) {
            foreach ($searchFields as $key => $value) {
                if (!is_null($value)) {
                    $method = $this->getSearchMethod($key, $value);
                    $case = $this->getSearchCase($key, $value);
                    $query['query']['bool']['must'][] = [$method => $case];
                } else {
                    $query['query']['bool']['must_not'][] = ['exists' => ['field' => $key]];
                }
            }
        }

        if ($nested && count($nested)) {
            foreach ($nested as $key => $value) {
                if (!is_null($value)) {
                    $method = $this->getSearchMethod($key, $value);
                    $case = null;
                    switch ($method) {
                        case self::TERMS:
                            $value = is_array($value) ? self::arrayToLower($value) : $value;
                            $case = [
                                'query' => [
                                    self::TERMS => [
                                        $key => is_array($value) ? $value : [$value],
                                    ]
                                ],
                                'path' => explode('.', $key)[0]
                            ];
                            break;
                        default:
                            $case = [
                                'query' => [
                                    self::MATCH_PHRASE_PREFIX => [$key => mb_strtolower($value)]
                                ],
                                'path' => explode('.', $key)[0]
                            ];
                    }
                    $query['query']['bool']['must'][] = ['nested' => $case];
                } else {
                    $query['query']['bool']['must_not'][] = ['exists' => ['field' => $key]];
                }
            }
        }

        if ($script && count($script)) {
            foreach ($script as $key => $value) {
                $query['query']['bool']['filter'][]['script'] = [
                    'script' => [
                        "source" => $value
                    ]
                ];
            }
        }

        $query['sort'] = $this->getSort($sort);

        return $query;
    }

    /**
     * @param null $sortFields
     * @return array
     */
    private function getSort($sortFields = null)
    {
        $id = true;
        $query = [];

        if (!is_null($sortFields)) {
            if (isset($sortFields['simple']) || isset($sortFields['script'])) {
                foreach ($sortFields as $key => $sortField) {
                    if (str_starts_with($sortField, '-')) {
                        $sortField = substr($sortField, 1);
                        $query[][$sortField]['order'] = 'desc';
                    } else {
                        $query[][$sortField] = ['order' => 'asc', 'missing' => '_first'];
                    }

                    if ($sortField == 'id') {
                        $id = false;
                    }
                }
            }
            if (isset($sortFields['nested'])) {
                foreach ($sortFields as $key => $sortField) {
                    if (str_starts_with($sortField, '-')) {
                        $sortField = substr($sortField, 1);
                        $q[$sortField] = ['order' => 'desc', 'missing' => '_first'];
                    } else {
                        $q[$sortField] = ['order' => 'asc', 'missing' => '_first'];
                    }
                    $q[$sortField]['nested']['path'] = explode('.', $sortField)[0];

                    if ($sortField == 'id') {
                        $id = false;
                    }

                    $query[] = $q;
                }
            }

            if ($id) {
                $query[]['id']['order'] = 'asc';
            }
        } else {
            $query[]['id']['order'] = 'asc';
        }

        return $query;
    }

    public static function arrayToLower(array $data)
    {
        return array_map(
            function ($item) {
                return mb_strtolower($item);
            },
            $data
        );
    }

    /**
     * @param $query
     * @param $aggregations
     * @return array
     */
    public function getStatsByAggregations($query, $aggregations)
    {
        if ($aggregations && count($aggregations)) {
            foreach ($aggregations as $method => $fieldAggs) {
                $case = null;
                switch ($method) {
                    case self::TERMS:
                        foreach ($fieldAggs as $key => $value) {
                            $value = is_array($value) ? self::arrayToLower($value) : $value;
                            $query['aggs'][$value] = [
                                self::TERMS => [
                                    'field' => $value
                                ]
                            ];
                        }
                        break;
                    case 'aggs':
                        foreach ($fieldAggs as $keyAggs => $valueAggs) {
                            switch ($keyAggs) {
                                case 'sum':
                                    foreach ($valueAggs as $key => $value) {
                                        $value = is_array($value) ? self::arrayToLower($value) : $value;
                                        $query['aggs'][$value] =
                                            [
                                                $keyAggs => [
                                                    "field" => $value,
                                                ]
                                            ];
                                    }
                                    break;
                                case 'top_hits':
                                    $query['aggs'][$keyAggs][$keyAggs] = [
                                        '_source' => [
                                            'includes' => $fieldAggs[$keyAggs]
                                        ],
                                        'size' => 1
                                    ];
                                    break;
                                default:
                                    break;
                            }
                        }
                        break;
                    default:
                        break;
                }
//                $query['aggs'][] = $case;
            }
        }

        return $query;
    }

    /**
     * @param $key
     * @param $value
     * @return array|array[]
     */
    public function getSearchCase($key, $value)
    {
        $method = $this->getSearchMethod($key, $value);

        return match ($method) {
            self::MATCH_PHRASE_PREFIX => [
                $key => [
                    'query' => mb_strtolower($value),
                    'max_expansions' => 50
                ]
            ],
            self::TERMS => [
                $key => is_array($value) ? array_values($value) : [$value],
            ],
            default => [$key => mb_strtolower($value)],
        };
    }
}
