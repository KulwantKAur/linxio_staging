<?php

namespace App\Service\FuelType;

use App\Entity\FuelType\FuelMapping;
use App\Entity\FuelType\FuelType;
use App\Entity\User;
use App\Exceptions\ValidationException;
use App\Service\BaseService;
use App\Service\ElasticSearch\ElasticSearch;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FuelMappingService extends BaseService
{
    protected $translator;
    private $em;
    private $fuelMappingFinder;
    private $eventDispatcher;
    private $validator;

    const ELASTIC_NESTED_FIELDS = [
    ];
    const ELASTIC_SIMPLE_FIELDS = [
        'name' => 'name',
        'fuelType' => 'fuelType.id',
        'status' => 'status'
    ];
    const ELASTIC_RANGE_FIELDS = [];


    /**
     * FuelMappingService constructor.
     * @param TranslatorInterface $translator
     * @param EntityManager $em
     * @param TransformedFinder $fuelMappingFinder
     * @param EventDispatcherInterface $eventDispatcher
     * @param ValidatorInterface $validator
     */
    public function __construct(
        TranslatorInterface $translator,
        EntityManager $em,
        TransformedFinder $fuelMappingFinder,
        EventDispatcherInterface $eventDispatcher,
        ValidatorInterface $validator
    ) {
        $this->translator = $translator;
        $this->em = $em;
        $this->fuelMappingFinder = $fuelMappingFinder;
        $this->eventDispatcher = $eventDispatcher;
        $this->validator = $validator;
    }

    /**
     * @param array $data
     * @param User $currentUser
     * @return FuelMapping
     * @throws ValidationException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(array $data, User $currentUser): FuelMapping
    {
        $this->validateFuelMappingFields($data, $currentUser);
        $data['fuelType'] = isset($data['fuelType'])
            ? $this->em->getRepository(FuelType::class)->findById($data['fuelType'])[0]
            : null;
        $fuelMapping = new FuelMapping($data);

        $this->validate($this->validator, $fuelMapping);

        $this->em->persist($fuelMapping);
        $this->em->flush();

        return $fuelMapping;
    }

    /**
     * @param array $params
     * @param User $user
     * @param bool $paginated
     * @return array
     */
    public function fuelMappingList(array $params, User $user, bool $paginated = true)
    {
        $fields = $this->prepareElasticFields($params);
        $elastica = new ElasticSearch($this->fuelMappingFinder);
        $fuelMapping = $elastica->find($fields, $fields['_source'] ?? [], $paginated);

        return $fuelMapping;
    }

    /**
     * @param int $id
     * @param User $user
     * @return FuelMapping|null
     */
    public function getById(int $id, User $user): ?FuelMapping
    {
        $fuelMapping = $this->em->getRepository(FuelMapping::class)->find($id);

        return !empty($fuelMapping) ? $fuelMapping : null;
    }

    /**
     * @param array $data
     * @param User $currentUser
     * @param FuelMapping $fuelMapping
     * @return FuelMapping
     * @throws ValidationException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function edit(array $data, User $currentUser, FuelMapping $fuelMapping): FuelMapping
    {
        $this->validateFuelMappingFields($data, $currentUser);

        $this->validate($this->validator, $fuelMapping);

        $data['updatedAt'] = new \DateTime();
        $data['fuelType'] = isset($data['fuelType'])
            ? $this->em->getRepository(FuelType::class)->find($data['fuelType'])
            : null;

        $fuelMapping->setAttributes($data);

        $this->em->flush();
        $this->em->refresh($fuelMapping);

        return $fuelMapping;
    }

    /**
     * @param FuelMapping $fuelMapping
     * @param User $currentUser
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove(FuelMapping $fuelMapping, User $currentUser)
    {
        $fuelMapping->setStatus(FuelMapping::STATUS_DELETED);

        $fuelMapping->setUpdatedAt(new \DateTime());
        $fuelMapping->setUpdatedBy($currentUser);

        $this->em->flush();
    }

    /**
     * @param array $fields
     * @param User $currentUser
     * @throws ValidationException
     */
    private function validateFuelMappingFields(array $fields, User $currentUser)
    {
        $errors = [];

        if (!($fields['name'] ?? null)) {
            $errors['name'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }

        if (!($fields['fuelType'] ?? null)) {
            $errors['fuelType'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }
}
