<?php

namespace App\Service\ElasticSearch;

use App\Entity\User;
use Elastica\Query;
use FOS\ElasticaBundle\Doctrine\ORM\ElasticaToModelTransformer;
use FOS\ElasticaBundle\Index\IndexManager;

class FullSearch
{
    private $indexManager;
    private $transformers;
    private $transformer;

    public const SEARCH_FIELDS = [
        'name',
        'legalName',
        'sn',
        'imei',
        'phone',
        'surname',
        'email',
        'fullname',
        'regNo',
        'vin',
        'defaultLabel',
        'device.id',
        'areaClientId',
        'sensorId',
        'label',
        'sensorBleId',
        'previousPhones'
    ];

    public function __construct(
        IndexManager $indexManager,
        string $env,
        ElasticaToModelTransformer $userTransformer,
        ElasticaToModelTransformer $clientTransformer,
        ElasticaToModelTransformer $deviceTransformer,
        ElasticaToModelTransformer $vehicleTransformer,
        ElasticaToModelTransformer $areaTransformer,
        ElasticaToModelTransformer $depotTransformer,
        ElasticaToModelTransformer $vehicleGroupTransformer,
        ElasticaToModelTransformer $areaGroupTransformer,
        ElasticaToModelTransformer $sensorTransformer,
        ElasticaToModelTransformer $assetTransformer
    ) {
        $this->indexManager = $indexManager;
        $this->transformers['user_' . $env] = $userTransformer;
        $this->transformers['client_' . $env] = $clientTransformer;
        $this->transformers['device_' . $env] = $deviceTransformer;
        $this->transformers['vehicle_' . $env] = $vehicleTransformer;
        $this->transformers['area_' . $env] = $areaTransformer;
        $this->transformers['depot_' . $env] = $depotTransformer;
        $this->transformers['vehicle_group_' . $env] = $vehicleGroupTransformer;
        $this->transformers['area_group_' . $env] = $areaGroupTransformer;
        $this->transformers['sensor_' . $env] = $sensorTransformer;
        $this->transformers['asset_' . $env] = $assetTransformer;
        $this->transformer = new ElasticaToModelTransformerCollection($this->transformers);
    }

    /**
     * @param string $query
     * @param User $currentUser
     * @param string $env
     * @return array
     * @throws \ReflectionException
     */
    public function search(string $query, User $currentUser, string $env)
    {
        $search = $this->indexManager->getIndex('user')->createSearch();
        $search->addIndex('user_' . $env);
        $search->addIndex('client_' . $env);
        $search->addIndex('device_' . $env);
        $search->addIndex('vehicle_' . $env);
        $search->addIndex('area_' . $env);
        $search->addIndex('depot_' . $env);
        $search->addIndex('vehicle_group_' . $env);
        $search->addIndex('area_group_' . $env);
        $search->addIndex('sensor_' . $env);
        $search->addIndex('asset_' . $env);

        $multiMatch = new Query\MultiMatch();
        $multiMatch->setParams([
            'query' => $query,
            'fields' => self::SEARCH_FIELDS,
            'type' => 'phrase_prefix',
            'max_expansions' => 100,
            "lenient" => true
        ]);

//        $vehicleQuery = (new Query\BoolQuery())
//            ->addMust(new Query\Exists('device.id'))
//            ->addFilter((new Query\Term())->setParam('_index', 'vehicle_' . $env))
//            ->addMust($multiMatch);

        $indexesQuery = (new Query\BoolQuery())
            ->addMust($multiMatch)
            ->addFilter((new Query\Terms('_index'))->setTerms([
                'user_' . $env,
                'client_' . $env,
                'device_' . $env,
                'vehicle_' . $env,
                'area_' . $env,
                'depot_' . $env,
                'vehicle_group_' . $env,
                'area_group_' . $env,
                'sensor_' . $env,
                'asset_' . $env,
            ]));

        if ($currentUser->isClientManager() && !$currentUser->isAllTeamsPermissions()) {
//            $vehicleQuery->addMust((new Query\Terms('team.id'))->setTerms($currentUser->getManagedTeamsIds()));
            $indexesQuery->addMust((new Query\Terms('team.id'))->setTerms($currentUser->getManagedTeamsIds()));
        }

        $bool = (new Query\BoolQuery())->addShould($indexesQuery);//->addShould($vehicleQuery);
        $query = (new Query($bool))->setParam('size', 50);

        $resultsSet = $search->search($query)->getResults();
        $results = $this->transformer->transform($resultsSet);

        $data = [];
        foreach ($results as $item) {
            $itemClass = strtolower((new \ReflectionClass($item))->getShortName());
            $data[$itemClass]['type'] = $itemClass;
            $data[$itemClass]['items'][] = $item->toArray();
        }

        return $data;
    }
}