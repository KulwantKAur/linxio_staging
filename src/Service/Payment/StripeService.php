<?php

namespace App\Service\Payment;

use App\Entity\Client;
use App\Entity\Invoice;
use App\Entity\StripeMandate;
use App\Entity\StripeSecret;
use App\Entity\Team;
use App\Exceptions\Billing\MissingPaymentMethodException;
use App\Exceptions\Billing\StripeIntegrationException;
use App\Service\BaseService;
use Doctrine\ORM\EntityManager;
use Stripe\Collection;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentMethod;
use Stripe\PaymentMethod as StripePaymentMethod;
use Stripe\StripeClient;

class StripeService extends BaseService
{
    private $apiKey;

    public const PAYMENT_METHOD_CARD = 'card';
    public const PAYMENT_METHOD_BECS_DEBIT = 'au_becs_debit';
    public const PAYMENT_METHOD_MANUAL = 'manual';

    public function __construct(private EntityManager $em)
    {

    }

    public function setApiKey($key)
    {
        $this->apiKey = $key;
    }

    public function hideSecret(StripeSecret $stripeSecret)
    {
        return [
            'publicKey' => substr($stripeSecret->getPublicKey(), 0, 11) . str_repeat('*',
                    20) . substr($stripeSecret->getPublicKey(), -4, 4),
            'secretKey' => substr($stripeSecret->getSecretKey(), 0, 11) . str_repeat('*',
                    20) . substr($stripeSecret->getSecretKey(), -4, 4),
        ];
    }

    public function saveAuthParams($data, $user)
    {
        $stripe = $this->stripe(['api_key' => $data['publicKey']]);
//        $this->validateSecret($stripe);

        $team = $user->getTeam();

        $stripeSecret = $this->em->getRepository(StripeSecret::class)->findOneBy(['team' => $team]);
        if ($stripeSecret) {
            return $this->updateSecret($data, $stripeSecret);
        } else {
            return $this->createSecret($data, $team);
        }
    }

    private function validateSecret($stripe)
    {
        $stripe->tokens->create([
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => 8,
                'exp_year' => date('Y') + 1,
                'cvc' => '314',
            ]
        ]);
    }

    public function createSetupIntent(Client $client)
    {
        $this->setApiKey($client->getOwnerTeam()->getStripeSecret()->getSecretKey());
        $this->createAsStripeCustomer($client);

        return $this->stripe()->setupIntents->create([
            'payment_method_types' => [StripeService::PAYMENT_METHOD_BECS_DEBIT, StripeService::PAYMENT_METHOD_CARD],
            'customer' => $client->getStripeId()
        ]);
    }

    private function createSecret(array $data, Team $team)
    {
        $connection = $this->em->getConnection();
        try {
            $connection->beginTransaction();

            $stripeSecret = new StripeSecret($data);
            $stripeSecret->setTeam($team);
            $this->em->persist($stripeSecret);
            $this->em->flush();

            $this->em->getConnection()->commit();

            return $stripeSecret;
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
    }

    private function updateSecret(array $data, StripeSecret $stripeSecret)
    {
        $connection = $this->em->getConnection();
        try {
            $connection->beginTransaction();
            $stripeSecret->setAttributes($data);
            $this->em->persist($stripeSecret);
            $this->em->flush();

            $this->em->getConnection()->commit();

            return $stripeSecret;
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
    }

    /**
     * @param Client $client
     * @return \Stripe\Customer
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function createAsStripeCustomer(Client $client)
    {
        if ($client->hasStripeId()) {
            return;
        }
        $customer = $this->stripe()->customers->create([
            'name' => $client->getName(),
            'address' => ['line1' => $client->getBillingAddress()]
        ]);

        $client->setStripeId($customer->id);
        $this->em->persist($client);
        $this->em->flush();
    }

    /**
     * Get the Stripe SDK client.
     *
     * @param array $options
     * @return \Stripe\StripeClient
     */
    public function stripe(array $options = [])
    {
        return new StripeClient(array_merge([
            'api_key' => $options['api_key'] ?? $this->apiKey,
            'stripe_version' => '2022-08-01',
        ], $options));
    }

    /**
     * @param $type
     * @param $parameters
     * @return Collection|PaymentMethod[]
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function getPaymentMethods($stripeId, $type = 'card', $parameters = [])
    {
        $parameters = array_merge(['limit' => 24], $parameters);

        $paymentMethods = $this->stripe()->paymentMethods->all(
            ['customer' => $stripeId, 'type' => $type] + $parameters
        );

        return $paymentMethods->data;
    }

    /**
     * Only for developer purposes
     *
     * @param $stripeId
     * @return void
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function addPaymentMethod($stripeId)
    {
        $stripePaymentMethod = $this->stripe()->paymentMethods->create([
            'type' => 'card',
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => 8,
                'exp_year' => 2023,
                'cvc' => '314',
            ],
        ]);

        if ($stripePaymentMethod->customer !== $stripeId) {
            $stripePaymentMethod = $stripePaymentMethod->attach(
                ['customer' => $stripeId]
            );
        }

        $this->stripe()->customers->update($stripeId, [
            'invoice_settings' => ['default_payment_method' => $stripePaymentMethod->id],
        ]);
    }

    public function getDefaultPaymentMethod(Client $client)
    {
        if (!$client->hasStripeId()) {
            return null;
        }

        /** @var \Stripe\Customer */
        $customer = $this->asStripeCustomer($client, ['default_source', 'invoice_settings.default_payment_method']);

        if ($customer->invoice_settings->default_payment_method) {
            return $customer->invoice_settings->default_payment_method;
        }

        // If we can't find a payment method, try to return a legacy source...
        return $customer->default_source;
    }

    public function asStripeCustomer(Client $client, $expand = [])
    {
        return $this->stripe()->customers->retrieve(
            $client->getStripeId(), ['expand' => $expand]
        );
    }

    public function charge($amount, $paymentMethod, $stripeId, $options = [])
    {
        $options = array_merge([
            'confirmation_method' => 'automatic',
            'confirm' => true,
            'payment_method' => $paymentMethod,
            'currency' => 'AUD',
            'amount' => $this->roundAmount($amount),
            'customer' => $stripeId,
            'payment_method_types' => [$paymentMethod->type]
        ], $options);


        return $this->stripe()->paymentIntents->create($options);
    }

    public function roundAmount($amount)
    {
        return round($amount, 2) * 100;
    }

    public function payInvoice(Invoice $invoice)
    {
        $payer = $invoice->getClient();
        if ($payer->isManualPayment()) {
            throw new \Exception('Not possible to charge client with manual payment method');
        }

        if (!$invoice->getOwnerTeam()->getStripeSecret()) {
            throw new StripeIntegrationException('Owner team is not connected to Stripe');
        }

        $this->setApiKey($invoice->getOwnerTeam()->getStripeSecret()->getSecretKey());

        if (!$payer->getStripeId()) {
            throw new \Exception('Payer is not connected to Stripe');
        }

        $paymentMethod = $this->getDefaultPaymentMethod($payer);

        if (!$paymentMethod) {
            throw new MissingPaymentMethodException('Payer does not have payment method');
        }

        try {
            $feeRegionHandler = FeeRegionFactory::getFeeRegionHandler($invoice->getOwnerTeam()->getStripeSecret()->getFeeRegion());
            $amount = $feeRegionHandler->getAmountByType(
                $invoice->getTotalWithPrepayment(), $paymentMethod->type, $paymentMethod->card?->country
            );
            $invoice->setStripeFee($feeRegionHandler->getFeeByType(
                $invoice->getTotalWithPrepayment(), $paymentMethod->type, $paymentMethod->card?->country)
            );

            return $this->charge(
                $amount,
                $paymentMethod,
                $payer->getStripeId(),
                [
                    'transfer_group' => sprintf('InvoiceId: %d', $invoice->getId()),
                    'receipt_email' => $invoice->getClient()?->getAccountingContact()?->getEmail()
                        ?? $invoice->getClient()?->getKeyContact()?->getEmail() ?? null
                ]
            );
        } catch (ApiErrorException $exception) {
            $invoice->setPaymentStatus(Invoice::PAYMENT_STATUS_ERROR);
            $this->em->flush();

            throw $exception;
        }
    }

    public function resolveStripePaymentMethod($paymentMethod)
    {
        if ($paymentMethod instanceof StripePaymentMethod) {
            return $paymentMethod;
        }

        return $this->stripe()->paymentMethods->retrieve($paymentMethod);
    }

    public function updateStripeCustomer(Client $client, $options)
    {
        return $this->stripe()->customers->update(
            $client->getStripeId(), $options
        );
    }

    public function saveMandateBySetupIntent($setupIntentId)
    {
        $stripe = $this->stripe();
        $setupIntent = $stripe->setupIntents->retrieve($setupIntentId);
        $mandate = $stripe->mandates->retrieve($setupIntent->mandate);

        $stripeMandate = new StripeMandate($mandate->toArray());
        $this->em->persist($stripeMandate);
        $this->em->flush();

        return $stripeMandate;
    }

    public function getMandateByPaymentMethod($paymentMethodId)
    {
        /** @var StripeMandate $mandate */
        $mandate = $this->em->getRepository(StripeMandate::class)->findOneBy(['paymentMethodId' => $paymentMethodId]);

        if (!$mandate) {
            return null;
        }

        return $this->stripe()->mandates->retrieve($mandate->getMandateId());
    }

    public function getPayment($paymentId)
    {
        return $this->stripe()->paymentIntents->retrieve($paymentId);
    }
}