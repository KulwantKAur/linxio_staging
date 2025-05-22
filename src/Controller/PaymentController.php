<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Notification\Event;
use App\Entity\Permission;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Payment\PaymentService;
use Stripe\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends BaseController
{
    public function __construct(
        private PaymentService $paymentService,
        private NotificationEventDispatcher $notificationDispatcher
    )
    {

    }

    #[Route('/payment/methods', methods: ['GET'])]
    public function getPaymentMethods()
    {
        /** @var Client $client */
        $client = $this->getUser()->getClient();

        try {
            $this->denyAccessUnlessGranted(Permission::BILLING_PAYMENT_CHANGE);

//            if (!$client->getOwnerTeam()->getStripeSecret()) {
//                throw new \Exception('Owner does not connected to Stripe');
//            }

            $this->paymentService->setOwnerTeam($client->getOwnerTeam());

            return $this->viewItem($this->paymentService->getPaymentMethods($client));
        } catch (AuthenticationException $exception) {
            $this->notificationDispatcher->dispatch(Event::STRIPE_INTEGRATION_ERROR, $client->getOwnerTeam(), null, [
                'message' => $exception->getMessage()
            ]);
            return $this->viewException($exception);
        } catch (\Exception $exception) {
            return $this->viewException($exception);
        }
    }

    #[Route('/payment/methods/{id}', requirements: ['id' => '.+'], methods: ['DELETE'])]
    public function removePaymentMethod($id)
    {
        /** @var Client $client */
        $client = $this->getUser()->getClient();

        try {
            $this->denyAccessUnlessGranted(Permission::BILLING_PAYMENT_CHANGE);

            $this->paymentService->setOwnerTeam($client->getOwnerTeam());
            $this->paymentService->removePaymentMethod($id);
            $this->paymentService->makeDefaultFirstPaymentMethod($client);

            return $this->viewItem(['success' => true]);
        } catch (AuthenticationException $exception) {
            $this->notificationDispatcher->dispatch(Event::STRIPE_INTEGRATION_ERROR, $client->getOwnerTeam(), null, [
                'message' => $exception->getMessage()
            ]);

            return $this->viewException($exception);
        } catch (\Exception $exception) {
            return $this->viewException($exception);
        }
    }

    #[Route('/payment/methods/{id}/default', requirements: ['id' => '.+'], methods: ['POST'])]
    public function makeDefaultPaymentMethod($id)
    {
        /** @var Client $client */
        $client = $this->getUser()->getClient();

        try {
            $this->denyAccessUnlessGranted(Permission::BILLING_PAYMENT_CHANGE);

            $this->paymentService->setOwnerTeam($client->getOwnerTeam());
            $this->paymentService->makeDefaultPaymentMethod($client, $id);

            return $this->viewItem(['success' => true]);
        } catch (AuthenticationException $exception) {
            $this->notificationDispatcher->dispatch(Event::STRIPE_INTEGRATION_ERROR, $client->getOwnerTeam(), null, [
                'message' => $exception->getMessage()
            ]);

            return $this->viewException($exception);
        } catch (\Exception $exception) {
            return $this->viewException($exception);
        }
    }

    #[Route('/payment/methods/manual', methods: ['POST'])]
    public function switchManualPayment(Request $request)
    {
        /** @var Client $client */
        $client = $this->getUser()->getClient();

        try {
            $this->denyAccessUnlessGranted(Permission::BILLING_PAYMENT_CHANGE);

            $this->paymentService->switchManualPayment($client, (bool)$request->query->get('enable'));

            return $this->viewItem(['success' => true]);
        } catch (\Exception $exception) {
            return $this->viewException($exception);
        }
    }
}