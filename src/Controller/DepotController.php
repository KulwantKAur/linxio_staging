<?php

namespace App\Controller;


use App\Entity\Depot;
use App\Entity\Permission;
use App\Service\Depot\DepotService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/depots')]
class DepotController extends BaseController
{
    private $depotService;

    public function __construct(DepotService $depotService)
    {
        $this->depotService = $depotService;
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::DEPOT_NEW, Depot::class);
        try {
            $depot = $this->depotService->create($request->request->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($depot);
    }

    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function edit(Request $request, $id): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted(Permission::DEPOT_EDIT, Depot::class);
            $depot = $this->depotService->getById($id, $this->getUser());
            if ($depot) {
                $this->denyAccessUnlessGranted(null, $depot->getTeam());
                $depot = $this->depotService->edit($request->request->all(), $this->getUser(), $depot);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($depot);
    }

    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getDepotById(Request $request, $id): JsonResponse
    {
        try {
            $depot = $this->depotService->getById($id, $this->getUser());
            if ($depot) {
                $this->denyAccessUnlessGranted(null, $depot->getTeam());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($depot);
    }

    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(Request $request, $id): JsonResponse
    {
        try {
            $depot = $this->depotService->getById($id, $this->getUser());
            if ($depot) {
                $this->denyAccessUnlessGranted(null, $depot->getTeam());
                $this->denyAccessUnlessGranted(Permission::DEPOT_DELETE, Depot::class);

                $this->depotService->removeDepot($depot, $request->query->all(), $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem('', [], Response::HTTP_NO_CONTENT);
    }

    #[Route('', methods: ['GET'])]
    public function depotList(Request $request): JsonResponse
    {
        try {
            $depots = $this->depotService->depotList($request->query->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($depots);
    }

    #[Route('/dropdown', methods: ['GET'])]
    public function depotListDropdown(Request $request): JsonResponse
    {
        try {
            $depots = $this->depotService->depotList($request->query->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($depots);
    }

    #[Route('/{id}/restore', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function restore(Request $request, $id): JsonResponse
    {
        try {
            $depot = $this->depotService->getById($id, $this->getUser());
            if ($depot) {
                $this->denyAccessUnlessGranted(null, $depot->getTeam());
                $this->denyAccessUnlessGranted(Permission::DEPOT_ARCHIVE, Depot::class);

                $this->depotService->restore($depot, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($depot);
    }

    #[Route('/{id}/archive', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function archive(Request $request, $id): JsonResponse
    {
        try {
            $depot = $this->depotService->getById($id, $this->getUser());
            if ($depot) {
                $this->denyAccessUnlessGranted(null, $depot->getTeam());
                $this->denyAccessUnlessGranted(Permission::DEPOT_ARCHIVE, Depot::class);

                $this->depotService->archive($depot, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($depot);
    }

}