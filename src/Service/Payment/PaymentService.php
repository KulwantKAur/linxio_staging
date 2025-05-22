<?php

namespace App\Service\Payment;

use App\Entity\Client;
use App\Entity\Invoice;
use App\Entity\Team;
use App\Service\BaseService;
use App\Service\Billing\BillingService;
use Doctrine\ORM\EntityManager;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;

class PaymentService extends BaseService
{
    /** @var Team */
    private $ownerTeam;

    public function __construct(
        private readonly StripeService $stripeService,
        private readonly EntityManager $em,
        private readonly BillingService $billingService
    ) {

    }


    public function setOwnerTeam(Team $team)
    {
        $this->ownerTeam = $team;
    }

    /**
     * @param Client $client
     * @return array
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function getPaymentMethods(Client $client)
    {
        if ((!$this->billingService->getBillingSettingByTeam($client->getOwnerTeam())?->getIsStripeEnabled() || !$client->getOwnerTeam()->getStripeSecret()) && !$client->isManualPayment()) {
            $this->switchManualPayment($client, true);
        }

        $methods = [
            StripeService::PAYMENT_METHOD_MANUAL => null,
            StripeService::PAYMENT_METHOD_CARD => [],
            StripeService::PAYMENT_METHOD_BECS_DEBIT => []
        ];

        if ($client->isAllowManualPayment()) {
            $methods[StripeService::PAYMENT_METHOD_MANUAL] = [
                'id' => 'manual',
                'type' => StripeService::PAYMENT_METHOD_MANUAL,
                'is_default' => $client->isManualPayment()
            ];
        }
        if ($this->billingService->getBillingSettingByTeam($client->getOwnerTeam())?->getIsStripeEnabled()
            && $client->getOwnerTeam()->getStripeSecret()
        ) {
            $this->stripeService->setApiKey($this->ownerTeam->getStripeSecret()->getSecretKey());
            $this->stripeService->createAsStripeCustomer($client);
            $default = $this->getDefaultPaymentMethod($client);

            foreach ($this->stripeService->getPaymentMethods(
                $client->getStripeId(), StripeService::PAYMENT_METHOD_CARD
            ) as $method) {
                $methods[StripeService::PAYMENT_METHOD_CARD][] = $this->formatPaymentMethod(
                    $method, !$client->isManualPayment() && $default && $method->id == $default['id']
                );
            }
            foreach ($this->stripeService->getPaymentMethods(
                $client->getStripeId(), StripeService::PAYMENT_METHOD_BECS_DEBIT
            ) as $method) {
                $methods[StripeService::PAYMENT_METHOD_BECS_DEBIT][] = $this->formatPaymentMethod(
                    $method, !$client->isManualPayment() && $default && $method->id == $default['id']
                );
            }
        }

        return $methods;
    }

    private function formatPaymentMethod(PaymentMethod $method, $isDefault = false)
    {
        if ($method->type == StripeService::PAYMENT_METHOD_CARD) {
            return [
                'id' => $method->id,
                'brand' => $method->card->brand,
                'last4' => $method->card->last4,
                'exp_month' => $method->card->exp_month,
                'exp_year' => $method->card->exp_year,
                'is_default' => $isDefault,
                'name' => $method->billing_details->name,
                'type' => $method->type,
            ];
        } else {
            if ($method->type == StripeService::PAYMENT_METHOD_BECS_DEBIT) {
                $mandate = $this->stripeService->getMandateByPaymentMethod($method->id);

                return [
                    'id' => $method->id,
                    'bsb_number' => $method->au_becs_debit->bsb_number,
                    'last4' => $method->au_becs_debit->last4,
                    'email' => $method->billing_details->email,
                    'name' => $method->billing_details->name,
                    'is_default' => $isDefault,
                    'type' => $method->type,
                    'mandate' => [
                        'url' => $mandate?->payment_method_details->au_becs_debit->url,
                        'status' => $mandate?->status
                    ]
                ];
            }
        }
    }

    public function getDefaultPaymentMethod(Client $client)
    {
        if (!$this->billingService->getBillingSettingByTeam($client->getOwnerTeam())?->getIsStripeEnabled() && !$client->isManualPayment()) {
            $this->switchManualPayment($client, true);
        }

        if ($client->isManualPayment()) {
            return [
                'id' => StripeService::PAYMENT_METHOD_MANUAL,
                'type' => StripeService::PAYMENT_METHOD_MANUAL,
                'is_default' => $client->isManualPayment()
            ];
        }

        $this->stripeService->setApiKey($client->getOwnerTeam()->getStripeSecret()->getSecretKey());

        $default = $this->stripeService->getDefaultPaymentMethod($client);
        if (!$default) {
            return null;
        }

        return $this->formatPaymentMethod($default, true);
    }

    public function payInvoice(Invoice $invoice)
    {
        if ($invoice->getTotalWithPrepayment() == 0) {
            return true;
        }
        if ($invoice->getStatus() == Invoice::STATUS_PAYMENT_PROCESSING) {
            $this->stripeService->setApiKey($invoice->getOwnerTeam()->getStripeSecret()->getSecretKey());
            $stripePayment = $this->stripeService->getPayment($invoice->getPaymentId());
        } else {
            $stripePayment = $this->stripeService->payInvoice($invoice);
        }

        if ($stripePayment->status == PaymentIntent::STATUS_SUCCEEDED) {
            $invoice->setPaymentId($stripePayment->id);
            $invoice->setStatus(Invoice::STATUS_PAID);
            $invoice->setPaymentStatus(Invoice::PAYMENT_STATUS_SUCCESS);
            $this->em->flush();

            return $invoice->getStatus();
        }

        if ($stripePayment->status == PaymentIntent::STATUS_PROCESSING) {
            $invoice->setPaymentId($stripePayment->id);
            $invoice->setStatus(Invoice::STATUS_PAYMENT_PROCESSING);
            $invoice->setPaymentStatus(Invoice::PAYMENT_STATUS_PROCESSING);
            $this->em->flush();

            return $invoice->getStatus();
        }

        $invoice->setPaymentStatus(Invoice::PAYMENT_STATUS_ERROR);
        $this->em->flush();

        return Invoice::STATUS_PAYMENT_ERROR;
    }

    public function removePaymentMethod($id)
    {
        $this->stripeService->setApiKey($this->ownerTeam->getStripeSecret()->getSecretKey());
        $paymentMethod = $this->stripeService->resolveStripePaymentMethod($id);
        $paymentMethod->detach();
    }

    public function makeDefaultPaymentMethod(Client $client, $id)
    {
        $this->switchManualPayment($client, false);
        $this->stripeService->setApiKey($this->ownerTeam->getStripeSecret()->getSecretKey());
        $this->stripeService->updateStripeCustomer($client, [
            'invoice_settings' => ['default_payment_method' => $id],
        ]);
    }

    public function makeDefaultFirstPaymentMethod(Client $client)
    {
        $defaultPaymentMethod = $this->getDefaultPaymentMethod($client);
        if (!$defaultPaymentMethod) {
            $allMethods = $this->getPaymentMethods($client);
            if (isset($allMethods[0]['id'])) {
                $this->makeDefaultPaymentMethod($client, $allMethods[0]['id']);
            }
        }
    }

    public function switchManualPayment(Client $client, $enable)
    {
        $client->setIsManualPayment($enable);
        $this->em->flush();
    }
}