<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Notification\Event;
use App\Entity\Permission;
use App\Entity\StripeSecret;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Payment\StripeService;
use Doctrine\ORM\EntityManager;
use Stripe\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class StripeController extends BaseController
{
    public function __construct(
        private StripeService $stripeService,
        private EntityManager $em,
        private NotificationEventDispatcher $notificationDispatcher
    ) {
    }

    #[Route('/stripe/secret', methods: ['POST'])]
    public function storeSecret(Request $request): JsonResponse
    {
        try {
            if (!$this->getUser()->isControlAdmin()) {
                throw new AccessDeniedException();
            }
            $data = $request->request->all();

            $this->stripeService->saveAuthParams($data, $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem(['success' => true]);
    }

    #[Route('/stripe/secret', methods: ['GET'])]
    public function getSecret(Request $request): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted(Permission::STRIPE_API);
            $team = $this->getUser()->getTeam();
            $stripeSecret = $this->em->getRepository(StripeSecret::class)->findOneBy(['team' => $team]);
            if (empty($stripeSecret)) {
                return $this->viewItem([]);
            }
            $stripeSecret = $this->stripeService->hideSecret($stripeSecret);

            return $this->viewItem($stripeSecret);

        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/stripe/setup-intent', methods: ['GET'])]
    public function getSetupIntent()
    {
        /** @var Client $client */
        $client = $this->getUser()->getClient();

        try {
            $this->denyAccessUnlessGranted(Permission::BILLING_PAYMENT_CHANGE, $client);

            $stripeSecret = $client->getOwnerTeam()->getStripeSecret();

            $this->stripeService->setApiKey($stripeSecret->getSecretKey());
            $intent = $this->stripeService->createSetupIntent($client);

            return $this->viewItem([
                'client_secret' => $intent->client_secret,
                'public_key' => $stripeSecret->getPublicKey(),
            ]);
        } catch (AuthenticationException $exception) {
            $this->notificationDispatcher->dispatch(Event::STRIPE_INTEGRATION_ERROR, $client->getOwnerTeam(), null, [
                'message' => $exception->getMessage()
            ]);
            return $this->viewException($exception);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/stripe/mandate', methods: ['POST'])]
    public function saveMandate(Request $request)
    {
        /** @var Client $client */
        $client = $this->getUser()->getClient();

        try {
            $this->denyAccessUnlessGranted(Permission::BILLING_PAYMENT_CHANGE, $client);

            $stripeSecret = $client->getOwnerTeam()->getStripeSecret();

            $this->stripeService->setApiKey($stripeSecret->getSecretKey());
            $this->stripeService->saveMandateBySetupIntent($request->get('setup_intent'));

            return $this->viewItem(['success' => true]);
        } catch (AuthenticationException $exception) {
            $this->notificationDispatcher->dispatch(Event::STRIPE_INTEGRATION_ERROR, $client->getOwnerTeam(), null, [
                'message' => $exception->getMessage()
            ]);
            return $this->viewException($exception);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Only for developer purposes
     */
    #[Route('/stripe/add-payment-method', methods: ['POST'])]
    public function addPaymentMethod()
    {
        try {
            /** @var Client $client */
            $client = $this->em->getRepository(Client::class)->find(1);
            $this->stripeService->setApiKey($client->getOwnerTeam()->getStripeSecret()->getSecretKey());
            $this->stripeService->addPaymentMethod($client->getStripeId());
        } catch (AuthenticationException $exception) {
            $this->notificationDispatcher->dispatch(Event::STRIPE_INTEGRATION_ERROR, $client->getOwnerTeam(), null, [
                'message' => $exception->getMessage()
            ]);
            return $this->viewException($exception);
        }
    }
}