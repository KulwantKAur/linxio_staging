<?php

namespace App\Controller;

use App\Entity\BillingEntityHistory;
use App\Entity\Device;
use App\Service\Billing\BillingEntityHistoryService;
use Doctrine\ORM\EntityManager;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class BillingEntityHistoryController extends BaseController
{
    private PaginatorInterface $paginator;
    private BillingEntityHistoryService $billingEntityHistoryService;
    private EntityManager $em;

    public function __construct(
        PaginatorInterface $paginator,
        BillingEntityHistoryService $billingEntityHistoryService,
        EntityManager $em
    ) {
        $this->paginator = $paginator;
        $this->billingEntityHistoryService = $billingEntityHistoryService;
        $this->em = $em;
    }

    #[Route('/device/{id}/team-history', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function deviceTeamHistory(Request $request, $id)
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        try {
            if (!$this->getUser()->isInAdminTeam()) {
                throw new AccessDeniedException();
            }

            $device = $this->em->getRepository(Device::class)->find($id);
            if ($device) {
                $this->denyAccessUnlessGranted(null, $device->getTeam());
                $query = $this->em->getRepository(BillingEntityHistory::class)
                    ->getDeviceTeamHistory($device);
                $pagination = $this->paginator->paginate($query, $page, $limit);
                $data = array_map(fn($item) => $item->toArray(), $pagination->getItems());
                $pagination->setItems($data);
            }

            return $this->viewItem($pagination);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }
}