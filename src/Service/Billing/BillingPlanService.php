<?php

namespace App\Service\Billing;

use App\Entity\BillingPlan;
use App\Entity\Client;
use App\Entity\User;
use App\Repository\Billing\ClientBilling;
use App\Service\BaseService;
use Doctrine\ORM\EntityManager;
use Symfony\Contracts\Translation\TranslatorInterface;

class BillingPlanService extends BaseService
{
    use BillingPlanValidationTrait;

    private TranslatorInterface $translator;
    private EntityManager $em;

    public function __construct(TranslatorInterface $translator, EntityManager $em)
    {
        $this->translator = $translator;
        $this->em = $em;
    }

    public function create(array $data, User $currentUser): ?BillingPlan
    {
        $this->validateCreateFields($data);

        $billingPlan = new BillingPlan($data);
        $billingPlan->setCreatedBy($currentUser);

        $this->em->persist($billingPlan);
        $this->em->flush();

        return $billingPlan;
    }

    public function edit(array $data, User $currentUser, BillingPlan $billingPlan): ?BillingPlan
    {
        $this->validateEditFields($data, $billingPlan);

        $newBillingPlan = $billingPlan->archive();

        $newBillingPlan->setAttributes($data);

        $newBillingPlan->setUpdatedBy($currentUser);
        $newBillingPlan->setUpdatedAt(new \DateTime());

        $this->em->persist($newBillingPlan);
        $this->em->flush();

        return $newBillingPlan;
    }

    public function delete(BillingPlan $billingPlan): BillingPlan
    {
        if ($billingPlan->getClients()->count()) {
            throw new \Exception('Plan has clients');
        }

        if ($billingPlan->getIsDefault()) {
            throw new \Exception('Default plan can\'t be deleted');
        }

        $billingPlan->setStatus(BillingPlan::STATUS_DELETED);
        $this->em->flush();

        return $billingPlan;
    }

    public function restore(BillingPlan $billingPlan): BillingPlan
    {
        $billingPlan->setStatus(BillingPlan::STATUS_ACTIVE);
        $this->em->flush();

        return $billingPlan;
    }

    public function copyBillingPlanToClient(Client $client, User $currentUser): ?BillingPlan
    {
        /** @var BillingPlan $billingPlan */
        $billingPlan = $this->em->getRepository(BillingPlan::class)
            ->findOneBy(['plan' => $client->getPlan(), 'team' => null]);

        if (!$billingPlan) {
            return null;
        }

        $copyBillingPlan = clone $billingPlan;
        $copyBillingPlan->setTeam($client->getTeam());
        $client->getTeam()->setBillingPlan($copyBillingPlan);
        $copyBillingPlan->setCreatedBy($currentUser);
        $copyBillingPlan->setCreatedAt(new \DateTime());
        $copyBillingPlan->setUpdatedAt(null);
        $copyBillingPlan->setUpdatedBy(null);

        $this->em->persist($copyBillingPlan);

        return $billingPlan;
    }

    public function getBillingPlanById($id): ?BillingPlan
    {
        return $this->em->getRepository(BillingPlan::class)->find($id);
    }

    public function getSubscriptionCost($teamId)
    {
        $subscriptionPrice = 0;
        $billingPlanDetails = $this->em->getRepository(Client::class)->getClientMomentBillingInfo($teamId)
            ->execute()
            ->fetchAssociative();

        if ($billingPlanDetails) {
            $subscriptionPrice = $billingPlanDetails[ClientBilling::activeVehicleTrackersTotal]
                + $billingPlanDetails[ClientBilling::deactivatedVehicleTrackersTotal]
                + $billingPlanDetails[ClientBilling::activePersonalTrackersTotal]
                + $billingPlanDetails[ClientBilling::deactivatedPersonalTrackersTotal]
                + $billingPlanDetails[ClientBilling::activeAssetTrackersTotal]
                + $billingPlanDetails[ClientBilling::deactivatedAssetTrackersTotal]
                + $billingPlanDetails[ClientBilling::virtualVehiclesTotal]
                + $billingPlanDetails[ClientBilling::archivedVehiclesTotal]
                + $billingPlanDetails[ClientBilling::activeSensorsTotal];
        }

        return $subscriptionPrice;
    }
}