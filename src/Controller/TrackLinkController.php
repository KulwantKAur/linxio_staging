<?php

namespace App\Controller;

use App\Entity\Permission;
use App\Entity\TrackLink;
use App\Entity\VehicleType;
use App\Service\TrackLink\TrackLinkService;
use App\Service\Vehicle\VehicleService;
use App\Util\PaginationHelper;
use Doctrine\ORM\EntityManager;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrackLinkController extends BaseController
{
    private $trackLinkService;
    private $vehicleService;
    private $paginator;

    public function __construct(
        TrackLinkService $trackLinkService,
        VehicleService $vehicleService,
        PaginatorInterface $paginator
    ) {
        $this->trackLinkService = $trackLinkService;
        $this->vehicleService = $vehicleService;
        $this->paginator = $paginator;
    }

    #[Route('/tracklink', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::TRACK_LINK_CREATE, TrackLink::class);
        try {
            $trackLink = $this->trackLinkService->create($request->request->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($trackLink, TrackLink::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/tracklink/{hash}', methods: ['GET'])]
    public function getByHash(Request $request, $hash): JsonResponse
    {
        try {
            $trackLinkData = $this->trackLinkService->getTrackLinkData($hash);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($trackLinkData);
    }

    #[Route('/tracklink/send/{hash}', methods: ['POST'])]
    public function sendLink(Request $request, $hash)
    {
        try {
            $trackLinkData = $this->trackLinkService->sendLink($hash, $request->request->all());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($trackLinkData);
    }

    #[Route('/tracklink/{hash}/vehicles/types', methods: ['GET'])]
    public function getVehicleTypesByTrackLinkHash(Request $request, $hash, EntityManager $em)
    {
        try {
            $page = $request->query->get('page', 1);
            $limit = $request->query->get('limit', 10);
            $fields = $request->query->get('fields') ?? [];

            $trackLink = $em->getRepository(TrackLink::class)->getByHash($hash);
            if (!$trackLink) {
                return $this->viewItem([], [], 200);
            }

            $vehicleTypes = $this->vehicleService->getVehicleTypes($trackLink->getCreatedBy(), $request->query->all());

            $pagination = $this->paginator->paginate(
                $vehicleTypes, $page, $limit, [PaginatorInterface::SORT_FIELD_PARAMETER_NAME => '~']
            );
            $pagination = PaginationHelper::paginationToEntityArray(
                $pagination, array_merge($fields, VehicleType::DEFAULT_DISPLAY_VALUES)
            );

            return $this->viewItem($pagination, [], 200);
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }
}