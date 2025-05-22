<?php

namespace App\Service\FuelType;

use App\Entity\FuelType\FuelIgnoreList;
use App\Entity\User;
use App\Exceptions\ValidationException;
use App\Service\BaseService;
use App\Service\ElasticSearch\ElasticSearch;
use FOS\ElasticaBundle\Persister\ObjectPersister;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FuelIgnoreListService extends BaseService
{
    protected $translator;
    private $em;
    private $fuelIgnoreFinder;
    private $eventDispatcher;
    private $validator;
    private $fuelIgnoreListPersister;

    const ELASTIC_NESTED_FIELDS = [];
    const ELASTIC_SIMPLE_FIELDS = [
        'name' => 'name',
    ];
    const ELASTIC_RANGE_FIELDS = [];

    /**
     * FuelIgnoreListService constructor.
     * @param TranslatorInterface $translator
     * @param EntityManager $em
     * @param TransformedFinder $fuelIgnoreFinder
     * @param EventDispatcherInterface $eventDispatcher
     * @param ValidatorInterface $validator
     * @param ObjectPersister $fuelIgnoreListPersister
     */
    public function __construct(
        TranslatorInterface $translator,
        EntityManager $em,
        TransformedFinder $fuelIgnoreFinder,
        EventDispatcherInterface $eventDispatcher,
        ValidatorInterface $validator,
        ObjectPersister $fuelIgnoreListPersister
    ) {
        $this->translator = $translator;
        $this->em = $em;
        $this->fuelIgnoreFinder = $fuelIgnoreFinder;
        $this->eventDispatcher = $eventDispatcher;
        $this->validator = $validator;
        $this->fuelIgnoreListPersister = $fuelIgnoreListPersister;
    }

    /**
     * @param array $data
     * @param User $currentUser
     * @return FuelIgnoreList
     * @throws ValidationException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(array $data, User $currentUser): FuelIgnoreList
    {
        $this->validateFuelIgnoreFields($data, $currentUser);
        $fuelIgnore = new FuelIgnoreList($data);

        $this->validate($this->validator, $fuelIgnore);

        $this->em->persist($fuelIgnore);
        $this->em->flush();

        return $fuelIgnore;
    }

    /**
     * @param array $params
     * @param User $user
     * @param bool $paginated
     * @return array
     */
    public function fuelIgnoreList(array $params, User $user, bool $paginated = true)
    {
        $fields = $this->prepareElasticFields($params);
        $elastica = new ElasticSearch($this->fuelIgnoreFinder);
        $fuelMapping = $elastica->find($fields, $fields['_source'] ?? [], $paginated);

        return $fuelMapping;
    }

    /**
     * @param int $id
     * @param User $user
     * @return FuelIgnoreList|null
     */
    public function getById(int $id, User $user): ?FuelIgnoreList
    {
        $fuelIgnore = $this->em->getRepository(FuelIgnoreList::class)->find($id);

        return !empty($fuelIgnore) ? $fuelIgnore : null;
    }

    /**
     * @param array $data
     * @param User $currentUser
     * @param FuelIgnoreList $fuelIgnore
     * @return FuelIgnoreList
     * @throws ValidationException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function edit(array $data, User $currentUser, FuelIgnoreList $fuelIgnore): FuelIgnoreList
    {
        $this->validateFuelIgnoreFields($data, $currentUser);

        $data['updatedAt'] = new \DateTime();
        $fuelIgnore->setAttributes($data);

        $this->validate($this->validator, $fuelIgnore);

        $this->em->flush();
        $this->em->refresh($fuelIgnore);

        return $fuelIgnore;
    }


    /**
     * @param $fuelIgnore
     * @return FuelIgnoreList
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove($fuelIgnore): FuelIgnoreList
    {
        $this->fuelIgnoreListPersister->deleteOne($fuelIgnore);
        $this->em->remove($fuelIgnore);
        $this->em->flush();

        sleep(1); // time for update elasticsearch (need for frontend)

        return $fuelIgnore;
    }

    /**
     * @param array $fields
     * @param User $currentUser
     * @throws ValidationException
     */
    private function validateFuelIgnoreFields(array $fields, User $currentUser)
    {
        $errors = [];

        if (!($fields['name'] ?? null)) {
            $errors['name'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }
}
