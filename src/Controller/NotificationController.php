<?php

namespace App\Controller;

use App\Entity\Acknowledge;
use App\Entity\EventLog\EventLog;
use App\Entity\Notification\Event;
use App\Entity\Notification\Notification;
use App\Entity\Notification\NotificationMobileDevice;
use App\Entity\Notification\TemplateSet;
use App\Entity\Permission;
use App\Entity\Vehicle;
use App\Enums\EntityHistoryTypes;
use App\Service\Asset\AssetService;
use App\Service\EntityHistory\EntityHistoryService;
use App\Service\Firebase\FCMService;
use App\Service\Notification\AcknowledgeService;
use App\Service\Notification\EventService;
use App\Service\Notification\AlertService;
use App\Service\Notification\MessageService;
use App\Service\Notification\NotificationCollectorService;
use App\Service\Notification\NotificationService;
use App\Service\Notification\TransportService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Google\Client;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotificationController extends BaseController
{
    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly EventService $eventService,
        private readonly TransportService $transportService,
        private readonly AlertService $alertService,
        private readonly MessageService $messageService,
        private readonly AcknowledgeService $acknowledgeService,
        private readonly AssetService $assetService,
        private readonly PaginatorInterface $paginator,
        private readonly EntityHistoryService $entityHistoryService,
        private readonly EntityManager $em
    ) {
    }

    #[Route('/notifications/event-types', methods: ['GET'])]
    public function getEvents(Request $request)
    {
        try {
            $events = $this->eventService->getUserEvents($request->query->all());
        } catch (\Exception $e) {
            return $this->viewError($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItemsArray($events);
    }

    #[Route('/notifications/transports', methods: ['GET'])]
    public function getTransports()
    {
        try {
            $transports = $this->transportService->getTransportsOptions($this->getUser());
        } catch (\Exception $e) {
            return $this->viewError($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItemsArray($transports);
    }

    #[Route('/notifications', methods: ['GET'])]
    public function getNotifications(Request $request)
    {
        $this->denyAccessUnlessGranted(Permission::NOTIFICATION_LIST, Notification::class);

        try {
            $notifications = $this->notificationService->notificationList($request->query->all(),
                $this->getUser()->getTeam());
        } catch (\Exception $e) {
            return $this->viewError($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($notifications);
    }

    #[Route('/notifications/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getNotificationById(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(Permission::NOTIFICATION_LIST, Notification::class);

        try {
            $notification = $this->notificationService->getById($id, $this->getUser());
            if (null === $notification) {
                throw new NotFoundHttpException();
            }
        } catch (\Exception $e) {
            return $this->viewError($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
        return $this->viewItem($notification);
    }

    #[Route('/notifications', methods: ['POST'])]
    public function create(Request $request)
    {
        $this->denyAccessUnlessGranted(Permission::NOTIFICATION_NEW, Notification::class);

        try {
            $notification = $this->notificationService->create($request->request->all(), $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($notification);
    }

    #[Route('/notifications/{id}', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function edit(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(Permission::NOTIFICATION_EDIT, Notification::class);

        try {
            $notification = $this->notificationService->getById($id, $this->getUser());

            if (null === $notification) {
                throw new NotFoundHttpException();
            }

            $notification = $this->notificationService->edit($notification, $request->request->all(), $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($notification);
    }

    #[Route('/notifications/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete($id)
    {
        $this->denyAccessUnlessGranted(Permission::NOTIFICATION_DELETE, Notification::class);

        try {
            $notification = $this->notificationService->getById($id, $this->getUser());

            if (null === $notification) {
                throw new NotFoundHttpException();
            }

            $notification = $this->notificationService->delete($notification, $this->getUser());
        } catch (\Exception $e) {
            return $this->viewError($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem([], [], Response::HTTP_NO_CONTENT);
    }

    #[Route('/notifications/alert/setting', methods: ['GET'])]
    public function getAlertSetting(Request $request)
    {
        try {
            $alertSetting = $this->alertService->getAlertSetting($request->query->all(), $this->getUser(), false);

            return $this->viewItem($alertSetting);
        } catch (\Exception $e) {
            return $this->viewError($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/notifications/messages', methods: ['GET'])]
    public function getNotificationMessages(Request $request)
    {
        $this->denyAccessUnlessGranted(Permission::NOTIFICATION_LIST, Notification::class);
        try {
            $messages = $this->messageService->getMessages($request->query->all(), $this->getUser());
            if (null === $messages) {
                throw new NotFoundHttpException();
            }

            $page = $request->query->get('page', 1);
            $limit = $request->query->get('limit', 10);
            $pagination = $this->paginator->paginate($messages, $page, $limit);
            $pagination->setItems($this->messageService->formatMessages($pagination));

            return $this->viewItem($pagination);
        } catch (\Exception $e) {
            return $this->viewError($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/notifications/list-days', methods: ['GET'])]
    public function getListEventTrackingDays()
    {
        try {
            $listDays = Notification::ALL_EVENT_TRACKING_DAYS;
        } catch (\Exception $e) {
            return $this->viewError($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($listDays);
    }

    #[Route('/notifications/messages/{id}/read', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function markAsReadNotificationMessage(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(Permission::NOTIFICATION_LIST, Notification::class);
        try {
            $message = $this->messageService->getById($id);

            if ($message) {
                $message = $this->messageService->markAsReadMessagesById(
                    array_merge(
                        $request->request->all(),
                        ['updatedBy' => $this->getUser()]
                    ),
                    $message
                );
            }
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($message, [], Response::HTTP_NO_CONTENT);
    }

    #[Route('/notifications/messages/read', methods: ['POST'])]
    public function markAsReadsAllMessagesByUser(Request $request)
    {
        $this->denyAccessUnlessGranted(Permission::NOTIFICATION_LIST, Notification::class);
        try {
            $this->messageService->markAsReadsAllMessagesByUser($request->request->all(), $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem('', [], Response::HTTP_NO_CONTENT);
    }

    #[Route('/notifications/messages/count', methods: ['GET'])]
    public function getCountUnreadNotificationMessages(Request $request)
    {
        $this->denyAccessUnlessGranted(Permission::NOTIFICATION_LIST, Notification::class);
        try {
            $data = $this->messageService->getCountUnreadMessages($request->query->all(), $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
        return $this->viewItem($data);
    }

    #[Route('/notifications/acknowledge/{id}', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function setNotificationAcknowledgeStatus(Request $request, $id)
    {
        try {
            /** @var Acknowledge $acknowledge */
            $acknowledge = $this->em->getRepository(Acknowledge::class)->find($id);
            $this->denyAccessUnlessGranted(null, $acknowledge->getNotification()->getOwnerTeam());

            $acknowledge = $this->acknowledgeService->updateAcknowledge(
                $acknowledge,
                $this->getUser(),
                $request->request->all()
            );
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($acknowledge);
    }

    #[Route('/notifications/acknowledge/{id}/history', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function acknowledgeHistoryList(Request $request, $id): JsonResponse
    {
        $acknowledge = $this->em->getRepository(Acknowledge::class)->find($id);
        $this->denyAccessUnlessGranted(null, $acknowledge->getNotification()->getOwnerTeam());

        try {
            $statusHistoryList =
                $this->entityHistoryService->list(
                    Acknowledge::class,
                    $acknowledge->getId(),
                    [
                        EntityHistoryTypes::ACKNOWLEDGE_CREATED,
                        EntityHistoryTypes::ACKNOWLEDGE_UPDATED
                    ]
                );
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItemsArray($statusHistoryList);
    }

    #[Route('/notifications/messages/vehicle/{vehicleId}', requirements: ['vehicleId' => '\d+'], methods: ['GET'])]
    public function getMessagesByVehicle(Request $request, TranslatorInterface $translator, $vehicleId)
    {
        $this->denyAccessUnlessGranted(Permission::NOTIFICATION_LIST, Notification::class);
        try {
            $vehicle = $this->em->getRepository(Vehicle::class)
                ->getVehicleById($this->getUser(), $vehicleId);

            if ($vehicle) {
                $this->denyAccessUnlessGranted(null, $vehicle->getTeam());
            } else {
                throw new NotFoundHttpException(
                    $translator->trans(
                        'entities.vehicle.id_does_not_exist',
                        ['%id%' => $vehicleId]
                    )
                );
            }

            $messages = $this->messageService->getMessagesByVehicle($request->query->all(), $this->getUser(), $vehicle);

            if (null === $messages) {
                throw new NotFoundHttpException(
                    $translator->trans('entities.message.not_found')
                );
            }

            $page = $request->query->get('page', 1);
            $limit = $request->query->get('limit', 10);
            $pagination = $this->paginator->paginate($messages, $page, $limit);
            $pagination->setItems($this->messageService->formatMessages($pagination));

            return $this->viewItem($pagination);
        } catch (\Exception $e) {
            return $this->viewError($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/notifications/messages/asset/{assetId}', requirements: ['assetId' => '\d+'], methods: ['GET'])]
    public function getMessagesByAsset(Request $request, TranslatorInterface $translator, $assetId)
    {
        $this->denyAccessUnlessGranted(Permission::NOTIFICATION_LIST, Notification::class);
        try {
            $asset = $this->assetService->getById($assetId, $this->getUser());

            if ($asset) {
                $this->denyAccessUnlessGranted(null, $asset->getTeam());
            } else {
                throw new NotFoundHttpException(
                    $translator->trans('entities.asset.id_does_not_exist', ['%id%' => $assetId])
                );
            }

            $messages = $this->messageService->getMessagesByAsset($request->query->all(), $this->getUser(), $asset);

            if (is_null($messages)) {
                throw new NotFoundHttpException($translator->trans('entities.message.not_found'));
            }

            $page = $request->query->get('page', 1);
            $limit = $request->query->get('limit', 10);
            $pagination = $this->paginator->paginate($messages, $page, $limit);
            $pagination->setItems($this->messageService->formatMessages($pagination));

            return $this->viewItem($pagination);
        } catch (\Exception $e) {
            return $this->viewError($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/notifications/templates', methods: ['GET'])]
    public function notificationsTemplates(Request $request): JsonResponse
    {
        $ntfTplList = $this->em->getRepository(TemplateSet::class)->findAll();

        return $this->viewItemsArray($ntfTplList);
    }

    /** @todo not using, just for tests */
    #[Route('/notifications/fcm/test', methods: ['POST'])]
    public function fcmTest(Request $request, FCMService $FCMService)
    {
        $params = $request->request->all();
        $title = $params['title'] ?? null;
        $body = $params['body'] ?? null;
        $extra = $params['extra'] ?? [];
        $batch = $params['batch'] ?? null;
        /** @var NotificationMobileDevice $devices */
        $device = $this->em->getRepository(NotificationMobileDevice::class)
            ->getLastLoggedDeviceByUserBy($this->getUser()->getId());
        $result = $FCMService->sendNotification(
            $device,
            $title,
            $body,
            $extra,
            $batch
        );

        return $this->viewItem($result);
    }

    /** @todo not using, just for tests */
    #[Route('/notifications/ntf/test', methods: ['POST'])]
    public function ntfTest(Request $request, NotificationCollectorService $notificationCollectorService)
    {
        $params = $request->request->all();
        $ntfEventId = $params['ntfEventId'] ?? null;
        $eventLogId = $params['eventLogId'] ?? null;
        $entityId = $params['entityId'] ?? null;
        $dt = $params['dt'] ? Carbon::parse($params['dt']) : new \DateTime();
        $ntfEvent = $this->em->getRepository(Event::class)->find($ntfEventId);
        $eventLog = $this->em->getRepository(EventLog::class)->find($eventLogId);
        $entity = $this->em->getRepository($ntfEvent->getEntity())->find($entityId);
        $notificationCollectorService->collect(
            $ntfEvent, $entity, $eventLog, $dt
        );

        return $this->viewItem($ntfEvent);
    }
}
