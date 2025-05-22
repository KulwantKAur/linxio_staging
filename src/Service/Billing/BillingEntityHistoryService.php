<?php

namespace App\Service\Billing;

use App\Entity\BillingEntityHistory;
use App\Entity\Device;
use App\Entity\Team;
use App\Service\BaseService;
use Doctrine\ORM\EntityManager;
use Knp\Component\Pager\PaginatorInterface;

class BillingEntityHistoryService extends BaseService
{
    public function __construct(
        private PaginatorInterface $paginator,
        private EntityManager      $em)
    {
    }

    public function create(array $data): BillingEntityHistory
    {
        $billingEntityHistory = new BillingEntityHistory($data);

        $this->em->persist($billingEntityHistory);
        $this->em->flush();

        return $billingEntityHistory;
    }

    public function update(array $data, ?BillingEntityHistory $billingEntityHistory)
    {
        if (!$billingEntityHistory) {
            return null;
        }

        $billingEntityHistory->setAttributes($data);
        $this->em->flush();

        return $billingEntityHistory;
    }

    public function getLastRecord(
        int $id,
        string $entityType,
        string $type,
        ?bool $isNullDateTo = true
    ): ?BillingEntityHistory {
        return $this->em->getRepository(BillingEntityHistory::class)
            ->getLastRecord($id, $entityType, $type, $isNullDateTo);
    }

    /**
     * @param int $id
     * @param string $entityType
     * @param string $type
     * @param \DateTimeInterface $dateFrom
     * @param bool|null $isNullDateTo
     * @return BillingEntityHistory|null
     */
    public function getRecordByDate(
        int $id,
        string $entityType,
        string $type,
        \DateTimeInterface $dateFrom,
        ?bool $isNullDateTo = true
    ): ?BillingEntityHistory {
        return $this->em->getRepository(BillingEntityHistory::class)
            ->getRecordByDate($id, $entityType, $type, $dateFrom, $isNullDateTo);
    }

    public function closeAndCreateRecord(
        int $entityId,
        string $entityType,
        string $type,
        Team $team,
        array $data = []
    ): ?BillingEntityHistory {
        $lastBillingEntity = $this->getLastRecord(
            $entityId,
            $entityType,
            $type
        );

        if ($type === BillingEntityHistory::TYPE_CREATE_DELETE && $lastBillingEntity
            && $team->getId() === $lastBillingEntity->getTeam()->getId()) {
            return $lastBillingEntity;
        }

        if ($lastBillingEntity) {
            $this->update(['dateTo' => new \DateTime()], $lastBillingEntity);
        } elseif (in_array($type, BillingEntityHistory::TYPES_FOR_CHECK_BEFORE_RECREATE)) {
            return null;
        }

        return $this->create([
            'entityId' => $entityId,
            'entity' => $entityType,
            'type' => $type,
            'dateFrom' => new \DateTime(),
            'team' => $team,
            'data' => $data
        ]);
    }

    public function onDeviceChangeTeam(Device $device)
    {
        //create history
        $this->closeAndCreateRecord(
            $device->getId(),
            BillingEntityHistory::ENTITY_DEVICE,
            BillingEntityHistory::TYPE_CREATE_DELETE,
            $device->getTeam()
        );

        //deactivation history
        $this->closeAndCreateRecord(
            $device->getId(),
            BillingEntityHistory::ENTITY_DEVICE,
            BillingEntityHistory::TYPE_DEACTIVATED,
            $device->getTeam()
        );

        //unavailable history
        $this->closeAndCreateRecord(
            $device->getId(),
            BillingEntityHistory::ENTITY_DEVICE,
            BillingEntityHistory::TYPE_UNAVAILABLE,
            $device->getTeam()
        );

        //change team history
        $this->closeAndCreateRecord(
            $device->getId(),
            BillingEntityHistory::ENTITY_DEVICE,
            BillingEntityHistory::TYPE_CHANGE_TEAM,
            $device->getTeam()
        );
    }
}
