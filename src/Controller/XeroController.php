<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Permission;
use App\Entity\Xero\XeroClientAccount;
use App\Entity\Xero\XeroClientSecret;
use App\Service\Xero\XeroInvoiceService;
use App\Service\Xero\XeroService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class XeroController extends BaseController
{

    public function __construct(
        private EntityManager $em,
        private XeroService $xeroService,
        private XeroInvoiceService $xeroInvoiceService
    ) {
    }

    #[Route('/xero/secret', methods: ['POST'])]
    public function storeXeroSecret(Request $request): JsonResponse
    {
        try {
            if (!$this->getUser()->isControlAdmin()) {
                throw new AccessDeniedException();
            }
            $data = $request->request->all();

            $this->xeroService->saveAuthParams($data, $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem(['success' => true]);
    }

    #[Route('/xero/secret', methods: ['GET'])]
    public function getXeroSecret(Request $request): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted(Permission::XERO_API);
            $team = $this->getUser()->getTeam();
            $xeroClientSecret = $this->em->getRepository(XeroClientSecret::class)->findOneBy(['team' => $team]);
            if (empty($xeroClientSecret)) {
                return $this->viewItem([]);
            }
            $xeroClientSecret = $this->xeroService->hideXeroClientSecret($xeroClientSecret);

            return $this->viewItem($xeroClientSecret);

        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/xero/client/contact', methods: ['POST'])]
    public function storeUserXeroContact(Request $request): JsonResponse
    {
        try {
            if (!$this->getUser()->isControlAdmin()) {
                throw new AccessDeniedException();
            }
            $data = $request->request->all();
            $team = $this->getUser()->getTeam();
            $client = $this->em->getRepository(Client::class)->find($data['clientId']);
            $xeroClientAccount = $this->em->getRepository(XeroClientAccount::class)->findOneBy([
                'team' => $team,
                'client' => $client
            ]);
            if ($xeroClientAccount) {
                $this->xeroService->updateCurrentUserContact($request->request->all(), $xeroClientAccount);
            } else {
                $this->xeroService->createCurrentUserContact($request->request->all(), $team, $client);
            }

        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem(['success' => true]);
    }

    #[Route('/xero/client/{clientId}/contact', requirements: ['clientId' => '\d+'], methods: ['GET'])]
    public function getUserXeroContact(Request $request, $clientId): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted(Permission::XERO_API);
            $team = $this->getUser()->getTeam();
            $client = $this->em->getRepository(Client::class)->find($clientId);
            $xeroClientAccount = $this->em->getRepository(XeroClientAccount::class)->findOneBy([
                'team' => $team,
                'client' => $client
            ]);

            return $this->viewItem($xeroClientAccount);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/xero/contacts', methods: ['GET'])]
    public function contacts(Request $request): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted(Permission::XERO_API);

            return $this->viewItem($this->xeroService->getContacts($request->query->all()));
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/xero/accounts', methods: ['GET'])]
    public function accounts(): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted(Permission::XERO_API);
            $tenantId = $this->getUser()->getTeam()->getXeroClientSecret()->getXeroTenantId();
            $accounts = $this->xeroInvoiceService->getAccounts($tenantId);

            return $this->viewItem($accounts);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }
}
