<?php

namespace App\Controller;

use App\Entity\Device;
use App\Entity\DeviceVendor;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\User;
use App\Exceptions\ValidationException;
use App\Response\CsvResponse;
use App\Service\Tracker\Factory\SimulatorTrackerFactory;
use App\Service\Tracker\Factory\TrackerFactory;
use App\Service\Tracker\Helper\LogHelper;
use App\Service\Tracker\TrackerService;
use App\Util\DateHelper;
use Doctrine\ORM\EntityManager;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Repository\TrackerHistoryBackupRepository;

class TrackerController extends BaseController
{
    /** @var TrackerService */
    private $trackerService;
    private $paginator;
    protected $em;
    protected $trackerFactory;
    protected $simulatorTrackerFactory;

    public function __construct(
        PaginatorInterface $paginator,
        EntityManager $em,
        TrackerFactory $trackerFactory,
        SimulatorTrackerFactory $simulatorTrackerFactory
    ) {
        $this->paginator = $paginator;
        $this->em = $em;
        $this->trackerFactory = $trackerFactory;
        $this->simulatorTrackerFactory = $simulatorTrackerFactory;
    }

    #[Route('/devices/logs/{type}', requirements: ['type' => 'json|csv'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function logs(Request $request, $type, TranslatorInterface $translator)
    {
        $imei = $request->query->get('imei');
        $dateFrom = $request->query->get('startDate');
        $dateTo = $request->query->get('endDate');
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);
        $fields = $request->query->all('fields');

        /** @var Device $device */
        $device = $this->em->getRepository(Device::class)->getDeviceByImei($imei);
        $this->trackerService = $this->trackerFactory->getInstance($device->getVendorName());
        $query = $this->trackerService->getQueryForDataLogByDevice($device, $dateFrom, $dateTo);

        try {
            switch ($type) {
                case 'json':
                    $pagination = $this->paginator->paginate($query, $page, $limit);
                    $pagination->setItems($this->trackerService->formatTrackerDataLog($pagination, $device));

                    return $this->viewItem($pagination);
                case 'csv':
                    return new CsvResponse(LogHelper::formatDataToCsv($query->getResult(), $this->getUser(), $translator, $fields));
            }
        } catch (\Exception $ex) {
            if ($ex instanceof ValidationException) {
                return $this->viewError($ex->getMessage(), Response::HTTP_BAD_REQUEST);
            }

            return $this->viewError($ex->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/tracker/unknown-devices', methods: ['GET'])]
    public function unknownDevices(Request $request)
    {
        $this->trackerService = $this->trackerFactory->getInstance(DeviceVendor::VENDOR_TELTONIKA);

        try {
            $unknownDevicesAuth = $this->trackerService->getUnknownDevicesAuth();
        } catch (\Exception $ex) {
            if ($ex instanceof ValidationException) {
                return $this->viewError($ex->getMessage(), Response::HTTP_BAD_REQUEST);
            }

            return $this->viewError($ex->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($unknownDevicesAuth);
    }

    #[Route('tracker/{id}/mobile-panic-button', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function clickMobilePanicButton(Request $request, $id, EntityManager $em)
    {
        // TODO: Permissions ??
        try {
            /** @var User $user */
            $user = $em->getRepository(User::class)->find($id);
            $device = $user->getDevice();

            if ($device) {
                $this->trackerService = $this->trackerFactory->getInstance($device->getVendorName(), $device);
                $this->trackerService->clickMobilePanicButton($device);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem([], [], Response::HTTP_NO_CONTENT);
    }

    #[Route('/tracker/map', methods: ['GET'], stateless: false)]
    public function map(Request $request, EntityManager $em, TranslatorInterface $translator)
    {
        $this->trackerService = $this->trackerFactory->getInstance(DeviceVendor::VENDOR_TELTONIKA);
        $imei = $request->query->get('imei');
        $dateFrom = DateHelper::formatDate($request->query->get('dateFrom'));
        $dateTo = DateHelper::formatDate($request->query->get('dateTo'));

        try {
            $device = $em->getRepository(Device::class)->getDeviceByImei($imei);

            if (!$device) {
                throw new NotFoundHttpException($translator->trans('services.tracker.device_not_found'));
            }

            $coordinates = $em->getRepository(TrackerHistory::class)
                ->getCoordinatesByDevice($device, $dateFrom, $dateTo);
        } catch (\Exception $ex) {
            if ($ex instanceof ValidationException) {
                return $this->viewError($ex->getMessage(), Response::HTTP_BAD_REQUEST);
            }

            return $this->viewError($ex->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return $this->render('default/map.html.twig', [
            'coordinates' => $coordinates,
            'google_maps_key' => getenv('GOOGLE_MAPS_KEY')
        ]);
    }


    
}
