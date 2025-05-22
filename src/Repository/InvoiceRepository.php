<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\Invoice;
use App\Entity\Setting;
use App\Entity\Team;
use Carbon\Carbon;
use Doctrine\Common\Collections\Criteria;

class InvoiceRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param $clientIds
     * @param array $filters
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getListByClients($clientIds, $filters = [])
    {
        if (!is_array($clientIds)) {
            $clientIds = (array)$clientIds;
        }

        if (empty($clientIds)) {
            $clientIds = [0];
        }

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $query = $queryBuilder
            ->select('invoice')
            ->from(Invoice::class, 'invoice')
            ->where('invoice.type = :regular')
            ->andWhere(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->in(
                        'invoice.client',
                        ':clientIds'
                    )
                )
            )
            ->setParameter('regular', Invoice::TYPE_REGULAR)
            ->setParameter('clientIds', $clientIds)
            ->groupBy('invoice.id')
            ->orderBy('invoice.createdAt', Criteria::DESC);

        if (isset($filters['status']) && $filters['status'] == Invoice::STATUS_NOT_PAID) {
            $query->andWhere('invoice.status = :not_paid')
                ->setParameter('not_paid', Invoice::STATUS_NOT_PAID);
        }

        return $query;
    }

    public function exists($clientId, $startDate, $endDate)
    {
        return (bool)$this->count([
            'clientId' => $clientId,
            'periodStart' => $startDate,
            'periodEnd' => $endDate,
        ]);
    }

    public function findReadyForPayment()
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('invoice')
            ->from(Invoice::class, 'invoice')
            ->join('invoice.client', 'client')
            ->andWhere('(invoice.status = :statusNotPaid OR invoice.status = :statusPaymentProcessing)')
            ->andWhere('invoice.paymentStatus != :paymentError OR invoice.paymentStatus IS NULL')
            ->andWhere('client.isManualPayment = \'false\'')
            ->andWhere('invoice.type = :regular')
            ->setParameter('statusNotPaid', Invoice::STATUS_NOT_PAID)
            ->setParameter('statusPaymentProcessing', Invoice::STATUS_PAYMENT_PROCESSING)
            ->setParameter('regular', Invoice::TYPE_REGULAR)
            ->setParameter('paymentError', Invoice::PAYMENT_STATUS_ERROR)
            ->orderBy('invoice.createdAt')
            ->getQuery()
            ->execute();
    }

    public function findReadyForExport()
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('invoice')
            ->from(Invoice::class, 'invoice')
            ->join('invoice.client', 'client')
            ->join('client.xeroClientAccount', 'xeroClientAccount')
            ->andWhere('xeroClientAccount.xeroContactId <> :empty')
            ->andWhere('invoice.extInvoiceId IS NULL')
            ->andWhere('invoice.type = :regular')
            ->orderBy('invoice.createdAt')
            ->setParameter('regular', Invoice::TYPE_REGULAR)
            ->setParameter('empty', '')
            ->getQuery()
            ->execute();
    }

    public function findNotPaidWithXeroInvoice()
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('invoice')
            ->from(Invoice::class, 'invoice')
            ->where('invoice.extInvoiceId IS NOT NULL')
            ->andWhere('invoice.paymentId IS NULL')
            ->andWhere('invoice.status IN (:notPaid, :paid)')
            ->andWhere('invoice.type = :regular')
            ->setParameter('notPaid', Invoice::STATUS_NOT_PAID)
            ->setParameter('paid', Invoice::STATUS_PAID)
            ->setParameter('regular', Invoice::TYPE_REGULAR)
            ->getQuery()
            ->execute();
    }

    public function findPaidWithoutXeroPayment()
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('invoice')
            ->from(Invoice::class, 'invoice')
            ->where('invoice.extInvoiceId IS NOT NULL')
            ->andWhere('invoice.extPaymentId IS NULL')
            ->andWhere('invoice.status = :paid')
            ->andWhere('invoice.type = :regular')
            ->setParameter('paid', Invoice::STATUS_PAID)
            ->setParameter('regular', Invoice::TYPE_REGULAR)
            ->getQuery()
            ->execute();
    }

    public function getPreviousPrepayment($clientId, ?Invoice $prepaymentInvoice)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('invoice')
            ->from(Invoice::class, 'invoice')
            ->where('invoice.clientId = :clientId')
            ->andWhere('invoice.type = :prepayment')
            ->setParameter('clientId', $clientId)
            ->setParameter('prepayment', Invoice::TYPE_PREPAYMENT)
            ->setMaxResults(1)
            ->orderBy('invoice.periodStart', Criteria::DESC);

        if ($prepaymentInvoice) {
            $query
                ->andWhere('invoice.id != :previousInvoiceId')
                ->setParameter('previousInvoiceId', $prepaymentInvoice->getId());
        }

        return $query
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function haveInvoices($clientId)
    {
        return (bool)$this->count([
            'clientId' => $clientId,
        ]);
    }

    public function findOldNotPaid($afterDays)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('invoice')
            ->from(Invoice::class, 'invoice')
            ->where('invoice.status = :statusNotPaid')
            ->andWhere('invoice.type = :regular')
            ->andWhere('invoice.periodEnd < :overdueFrom')
            ->setParameter('statusNotPaid', Invoice::STATUS_NOT_PAID)
            ->setParameter('regular', Invoice::TYPE_REGULAR)
            ->setParameter('overdueFrom', Carbon::now()->subDays($afterDays)->toDateString())
            ->getQuery()
            ->execute();
    }

    public function haveOverdueByClientId($clientId): bool
    {
        return (bool)$this->count([
            'clientId' => $clientId,
            'status' => Invoice::STATUS_OVERDUE,
            'type' => Invoice::TYPE_REGULAR,
        ]);
    }

    public function haveNotPaidByClientId($clientId): bool
    {
        return (bool)$this->count([
            'clientId' => $clientId,
            'status' => Invoice::STATUS_NOT_PAID,
            'type' => Invoice::TYPE_REGULAR
        ]);
    }

    public function lastFailedPaymentByClientId($clientId): ?Invoice
    {
        return $this->findOneBy([
            'clientId' => $clientId,
            'status' => Invoice::STATUS_NOT_PAID,
            'type' => Invoice::TYPE_REGULAR,
            'paymentStatus' => Invoice::PAYMENT_STATUS_ERROR,
        ], ['createdAt' => Criteria::DESC]);
    }

    public function findOverdueInvoices()
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('invoice')
            ->from(Invoice::class, 'invoice')
            ->join('invoice.client', 'client')
            ->join('client.team', 'team')
            ->join('team.settings', 'settings')
            ->andWhere('invoice.dueAt < :now')
            ->andWhere('invoice.status = :statusNotPaid')
            ->andWhere('invoice.type = :regular')
            ->andWhere('settings.name = :settingName')
            ->andWhere('settings.value = :settingValue')
            ->setParameter('statusNotPaid', Invoice::STATUS_NOT_PAID)
            ->setParameter('regular', Invoice::TYPE_REGULAR)
            ->setParameter('now', new \DateTime())
            ->setParameter('settingName', Setting::BILLING)
            ->setParameter('settingValue', json_encode(true))
            ->getQuery()
            ->execute();
    }

    public function findOverdueInvoicesByClientTeam(int $teamId)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('invoice')
            ->from(Invoice::class, 'invoice')
            ->join('invoice.client', 'client')
            ->join('client.team', 'team')
            ->andWhere('invoice.dueAt < :now')
            ->andWhere('invoice.status = :statusNotPaid')
            ->andWhere('invoice.type = :regular')
            ->andWhere('team.id = :teamId')
            ->setParameter('statusNotPaid', Invoice::STATUS_NOT_PAID)
            ->setParameter('regular', Invoice::TYPE_REGULAR)
            ->setParameter('now', new \DateTime())
            ->setParameter('teamId', $teamId)
            ->getQuery()
            ->execute();
    }

    public function findWithoutXero(Client $client, string $type)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $this->getEntityManager()->createQueryBuilder()
            ->select('invoice')
            ->from(Invoice::class, 'invoice')
//            ->andWhere('invoice.extInvoiceId NULL')
//            ->andWhere('invoice.extInvoiceId <> :extInvoiceError')
            ->andWhere('invoice.type = :type')
            ->andWhere('invoice.client = :client')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isNull('invoice.extInvoiceId'),
                    $qb->expr()->eq('invoice.extInvoiceId', ':extInvoiceError'),
                )
            )
            ->setParameter('type', $type)
            ->setParameter('client', $client)
            ->setParameter('extInvoiceError', Invoice::EXT_INVOICE_ID_ERROR)
            ->getQuery()
            ->execute();
    }
}
