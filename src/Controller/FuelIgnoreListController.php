<?php

namespace App\Controller;

use App\Entity\FuelType\FuelIgnoreList;
use App\Entity\Permission;
use App\Service\FuelType\FuelIgnoreListService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class FuelIgnoreListController extends BaseController
{
    private $fuelIgnoreService;

    public function __construct(FuelIgnoreListService $fuelIgnoreService)
    {
        $this->fuelIgnoreService = $fuelIgnoreService;
    }

    #[Route('/fuel-ignored', methods: ['GET'])]
    public function getFuelIgnoredList(Request $request)
    {
        $this->denyAccessUnlessGranted(Permission::FUEL_IGNORE_LIST, FuelIgnoreList::class);
        try {
            $fuelIgnore = $this->fuelIgnoreService->fuelIgnoreList($request->query->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($fuelIgnore);
    }

    #[Route('/fuel-ignored', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::FUEL_IGNORE_NEW, FuelIgnoreList::class);
        try {
            $fuelIgnore = $this->fuelIgnoreService->create(
                array_merge(
                    $request->request->all(),
                    ['createdBy' => $this->getUser()]
                ),
                $this->getUser()
            );
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($fuelIgnore, FuelIgnoreList::DISPLAYED_VALUES);
    }

    #[Route('/fuel-ignored/{id}', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function edit(Request $request, $id): JsonResponse
    {
        try {
            $fuelIgnore = $this->fuelIgnoreService->getById($id, $this->getUser());
            if ($fuelIgnore) {
                $this->denyAccessUnlessGranted(Permission::FUEL_IGNORE_EDIT, $fuelIgnore);
                $fuelIgnore = $this->fuelIgnoreService->edit(
                    array_merge(
                        $request->request->all(),
                        ['updatedBy' => $this->getUser()]
                    ),
                    $this->getUser(),
                    $fuelIgnore
                );
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($fuelIgnore, FuelIgnoreList::DISPLAYED_VALUES);
    }

    #[Route('/fuel-ignored/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(Request $request, $id): JsonResponse
    {
        try {
            $fuelIgnore = $this->fuelIgnoreService->getById($id, $this->getUser());
            if ($fuelIgnore) {
                $this->denyAccessUnlessGranted(Permission::FUEL_IGNORE_DELETE, $fuelIgnore);
                $this->fuelIgnoreService->remove($fuelIgnore);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem('', [], Response::HTTP_NO_CONTENT);
    }

    #[Route('/fuel-ignored/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getFuelIgnoreById(Request $request, $id): JsonResponse
    {
        try {
            $fuelIgnore = $this->fuelIgnoreService->getById($id, $this->getUser());
            if ($fuelIgnore) {
                $this->denyAccessUnlessGranted(Permission::FUEL_IGNORE_LIST, $fuelIgnore);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($fuelIgnore, FuelIgnoreList::DISPLAYED_VALUES);
    }
}
