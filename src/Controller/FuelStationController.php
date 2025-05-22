<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\FuelStation;
use App\Entity\Permission;
use App\Service\FuelStation\FuelStationService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class FuelStationController extends BaseController
{
    public function __construct(
        private readonly FuelStationService $fuelStationService,
        private readonly EntityManager $em
    ) {
    }

    #[Route('/fuel/station', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $params = $request->request->all();
        try {
            $this->denyAccessUnlessGranted(Permission::FUEL_STATION_CREATE, FuelStation::class);
            $fuelStation = $this->fuelStationService->create($params, $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($fuelStation);
    }

    #[Route('/fuel/station/{id}', requirements: ["id" => "\d+"], methods: ['PATCH'])]
    public function edit(Request $request, int $id): JsonResponse
    {
        $params = $request->request->all();
        try {
            $this->denyAccessUnlessGranted(Permission::FUEL_STATION_EDIT, FuelStation::class);
            $fuelStation = $this->em->getRepository(FuelStation::class)->find($id);
            $this->denyAccessUnlessGranted(null, $fuelStation->getTeam());

            $fuelStation = $this->fuelStationService->edit($fuelStation, $params, $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($fuelStation);
    }

    #[Route('/fuel/station/{id}', requirements: ["id" => "\d+"], methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted(Permission::FUEL_STATION_DELETE, FuelStation::class);
            $fuelStation = $this->em->getRepository(FuelStation::class)->find($id);
            $this->denyAccessUnlessGranted(null, $fuelStation->getTeam());

            $this->fuelStationService->delete($fuelStation);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem('', [], Response::HTTP_NO_CONTENT);
    }

    #[Route('/fuel/station/{id}', requirements: ["id" => "\d+"], methods: ['GET'])]
    public function getById(int $id): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted(Permission::FUEL_STATION_LIST, FuelStation::class);
            $fuelStation = $this->em->getRepository(FuelStation::class)->find($id);
            $this->denyAccessUnlessGranted(null, $fuelStation->getTeam());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($fuelStation);
    }

    #[Route('/fuel/station', methods: ['GET'])]
    public function getList(Request $request): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted(Permission::FUEL_STATION_LIST, FuelStation::class);
            $list = $this->fuelStationService->getListByTeam($this->getUser()->getTeam(), $request->query->all());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($list);
    }

    #[Route('/fuel/station/file/upload', methods: ['POST'])]
    public function uploadFile(Request $request): JsonResponse
    {
        try {
            $params = array_merge_recursive($request->request->all(), ['files' => $request->files]);
            $list = $this->fuelStationService->parseFiles($params, $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($list);
    }

    #[Route('/fuel/station/file/save/{id}', requirements: ["id" => "\d+"], methods: ['POST'])]
    public function saveFile(Request $request, int $id): JsonResponse
    {
        try {
            $file = $this->em->getRepository(File::class)->find($id);
            if (!$file) {
                throw new FileNotFoundException();
            }
            if ($this->getUser()->getId() !== $file->getCreatedBy()->getId()) {
                throw new AccessDeniedException();
            }

            $list = $this->fuelStationService->saveImportFile($file, $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($list);
    }
}
