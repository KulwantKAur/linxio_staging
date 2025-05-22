<?php

namespace App\Service\FuelType;

use App\Entity\FuelType\FuelType;
use App\Entity\User;
use App\Service\BaseService;
use App\Service\ElasticSearch\ElasticSearch;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Contracts\Translation\TranslatorInterface;

class FuelTypeService extends BaseService
{
    protected $translator;
    private $em;
    private $fuelTypeFinder;

    public const ELASTIC_NESTED_FIELDS = [];
    public const ELASTIC_SIMPLE_FIELDS = [
        'id' => 'id',
        'name' => 'name',
    ];
    public const ELASTIC_RANGE_FIELDS = [];


    /**
     * FuelMappingService constructor.
     * @param TranslatorInterface $translator
     * @param EntityManager $em
     * @param TransformedFinder $fuelTypeFinder
     */
    public function __construct(
        TranslatorInterface $translator,
        EntityManager $em,
        TransformedFinder $fuelTypeFinder
    ) {
        $this->translator = $translator;
        $this->em = $em;
        $this->fuelTypeFinder = new ElasticSearch($fuelTypeFinder);
    }

    public function fuelTypeList(array $params, User $user, bool $paginated = true)
    {
        $fields = $this->prepareElasticFields($params);
        $params['fields'] = array_merge(FuelType::DISPLAYED_VALUES, $params['fields'] ?? []);

        return $this->fuelTypeFinder->find($fields, $fields['_source'] ?? [], $paginated, $user);
    }
}
