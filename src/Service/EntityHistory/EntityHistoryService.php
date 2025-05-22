<?php

namespace App\Service\EntityHistory;

use App\Entity\BaseEntity;
use App\Entity\EntityHistory;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EntityHistoryService
{
    private $em;
    private $tokenStorage;

    /**
     * ClientService constructor.
     * @param TokenStorageInterface $tokenStorage
     * @param EntityManager $em
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        EntityManager $em
    ) {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }

    public function create(
        BaseEntity $entity,
        $payload,
        $type,
        User $createdBy = null,
        int $currentUserId = null //PASS IT instead of createdBy for creating history from preUpdate method
    ): EntityHistory
    {
        $entityHistory = $this->em->getRepository(EntityHistory::class)
            ->findOneByEntityIdAndType($entity->getId(), $type);

        if (!$entityHistory || $entityHistory->getPayload() != $payload) {
            $loggedUser = $this->tokenStorage->getToken()
            && $this->tokenStorage->getToken()->getUser() instanceof User
                ? $this->tokenStorage->getToken()->getUser()
                : null;
            $entityHistory = new EntityHistory();
            $entityHistory->setEntity(ClassUtils::getClass($entity));
            $entityHistory->setEntityId($entity->getId());
            if (!$currentUserId) {
                $entityHistory->setCreatedBy($createdBy ?: $loggedUser);
            }
            $entityHistory->setPayload($payload ?? '');
            $entityHistory->setType($type);

            if ($entityHistory->getCreatedBy()) {
                $entityHistory->setEmail($entityHistory->getCreatedBy()->getEmail());
            }

            if (!$currentUserId) {
                $this->em->merge($entityHistory);
            } else {
                $this->em->persist($entityHistory);
            }
            $this->em->flush();
            if ($currentUserId) {
                $this->em->createQuery("UPDATE App:EntityHistory h SET h.createdBy = $currentUserId WHERE h.id = " . $entityHistory->getId())->getResult();
            }

        }

        return $entityHistory;
    }

    /**
     * @param $entity
     * @param $entityId
     * @param $type
     * @return array
     */
    public function list($entity = null, $entityId = null, $type = null)
    {
        $criteria = $this->getCriteria($entity, $entityId, $type);

        return $this->em->getRepository(EntityHistory::class)->findAllByCriteria($criteria);
    }

    public function listPagination($entity = null, $entityId = null, $type = null)
    {
        $criteria = $this->getCriteria($entity, $entityId, $type);

        return $this->em->getRepository(EntityHistory::class)->findQueryByCriteria($criteria);
    }

    /**
     * @param null $entity
     * @param null $entityId
     * @param null $type
     * @param array $exclude
     * @return ArrayCollection
     */
    public function listWithExclude($entity = null, $entityId = null, $type = null, array $exclude = [])
    {
        $criteria = $this->getCriteria($entity, $entityId, $type);

        return new ArrayCollection(
            $this->em->getRepository(EntityHistory::class)->findAllWithExclude($criteria, $exclude)
        );
    }

    /**
     * @param $entityId
     * @param $type
     * @return array
     */
    public function getByEntityIdAndType($entityId, $type)
    {
        return $this->em->getRepository(EntityHistory::class)->findOneByEntityIdAndType($entityId, $type);
    }

    /**
     * @param $entity
     * @param $entityId
     * @param $type
     * @return array
     */
    public function getLastByEntityAndEntityIdAndType($entity, $entityId, $type)
    {
        $criteria = $this->getCriteria($entity, $entityId, $type);

        return $this->em->getRepository(EntityHistory::class)->findLastByCriteria($criteria);
    }

    /**
     * @param $entity
     * @param $entityId
     * @param $type
     * @return array
     */
    private function getCriteria($entity, $entityId, $type)
    {
        $criteria = [];

        if ($entity) {
            $criteria['entity'] = $entity;
        }

        if ($entityId) {
            $criteria['entityId'] = $entityId;
        }

        if ($type) {
            $criteria['type'] = $type;
        }

        return $criteria;
    }

    public function setEntityManager($em)
    {
        $this->em = $em;
    }

}