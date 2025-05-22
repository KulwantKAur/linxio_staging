<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Device;
use App\Entity\Note;
use App\Entity\Permission;
use App\Entity\PlatformSetting;
use App\Entity\Reseller;
use App\Entity\User;
use App\Enums\EntityHistoryTypes;
use App\Response\CsvResponse;
use App\Service\Client\ClientService;
use App\Service\Device\DeviceService;
use App\Service\EntityHistory\EntityHistoryService;
use App\Service\Note\NoteService;
use App\Service\PlatformSetting\PlatformSettingService;
use App\Service\Reseller\ResellerService;
use App\Service\User\UserService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ResellerController extends BaseController
{
    private $resellerService;
    private $translator;
    private $userService;
    private $noteService;
    private $entityHistoryService;
    private $clientService;
    private $platformSettingService;

    public function __construct(
        ResellerService $resellerService,
        TranslatorInterface $translator,
        UserService $userService,
        NoteService $noteService,
        EntityHistoryService $entityHistoryService,
        ClientService $clientService,
        PlatformSettingService $platformSettingService
    ) {
        $this->resellerService = $resellerService;
        $this->userService = $userService;
        $this->translator = $translator;
        $this->noteService = $noteService;
        $this->entityHistoryService = $entityHistoryService;
        $this->clientService = $clientService;
        $this->platformSettingService = $platformSettingService;
    }

    #[Route('/reseller', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::RESELLER_NEW, Reseller::class);
        try {
            $data = $request->request->all();
            $data['logo'] = $request->files->get('logo') ?? null;
            $data['favicon'] = $request->files->get('favicon') ?? null;

            $reseller = $this->resellerService->create($data, $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($reseller, Reseller::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/reseller/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getResellerById(Request $request, $id): JsonResponse
    {
        try {
            $reseller = $this->resellerService->getById($id);
            if ($reseller) {
                $this->denyAccessUnlessGranted(null, $reseller->getTeam());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($reseller,
            array_merge(Reseller::DEFAULT_DISPLAY_VALUES,
                ['manager', 'salesManager', 'keyContact', 'plan', 'updatedBy', 'createdByName', 'teamWithIsChevron']));
    }

    #[Route('/reseller/{type}', requirements: ['type' => 'json|csv'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function resellerList(Request $request, $type): Response
    {
        $params = $request->query->all();
        $this->denyAccessUnlessGranted(Permission::RESELLER_LIST, Reseller::class);
        try {
            switch ($type) {
                case 'json':
                    $resellers = $this->resellerService->resellerList($params);

                    return $this->viewItem($resellers);
                case 'csv':
                    $resellers = $this->resellerService->resellerList($params, false);
                    $resellers = $this->resellerService
                        ->translateEntityArrayForExport($resellers, $params['fields'] ?? [], Reseller::class);

                    return new CsvResponse($resellers);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/reseller/{id}', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function edit(Request $request, $id): JsonResponse
    {
        try {
            $reseller = $this->resellerService->getById($id, $this->getUser());
            if ($reseller) {
                $this->denyAccessUnlessGranted(Permission::RESELLER_EDIT, $reseller);
                $this->denyAccessUnlessGranted(null, $reseller->getTeam());

                $data = $request->request->all();
                $data['logo'] = $request->files->get('logo') ?? null;
                $data['favicon'] = $request->files->get('favicon') ?? null;

                $reseller = $this->resellerService->edit($reseller, $data, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($reseller, Reseller::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/reseller/{id}/users', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function createResellerUser(Request $request, $id): JsonResponse
    {
        try {
            $reseller = $this->resellerService->getById($id, $this->getUser());
            $user = null;
            if ($reseller) {
                $this->denyAccessUnlessGranted(Permission::RESELLER_USER_NEW, $reseller);
                $this->denyAccessUnlessGranted(null, $reseller->getTeam());
                $data = $request->request->all();
                $data['picture'] = $request->files->get('logo') ?? null;

                $user = $this->resellerService->createResellerUser($reseller, $data, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($user, User::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/reseller/{id}/users/{userId}', requirements: ['id' => '\d+', 'userId' => '\d+'], methods: ['POST'])]
    public function editResellerUser(Request $request, $id, $userId): JsonResponse
    {
        try {
            $reseller = $this->resellerService->getById($id);
            $user = null;
            if ($reseller) {
                if ($this->getUser()->getId() !== $userId) {
                    $this->denyAccessUnlessGranted(Permission::RESELLER_USER_EDIT, $reseller);
                }

                $this->denyAccessUnlessGranted(null, $reseller->getTeam());
                $user = $this->resellerService->getResellerUser($userId, $reseller);

                $data = $request->request->all();
                $data['avatar'] = $request->files->get('avatar') ?? null;

                $user = $this->resellerService->editResellerUser($reseller, $user, $data, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($user, array_merge(
            User::DEFAULT_DISPLAY_VALUES,
            [
                'blockingMessage',
                'isBlocked',
            ]
        ));
    }

    #[Route('/reseller/{id}/users/{userId}', requirements: ['id' => '\d+', 'userId' => '\d+'], methods: ['GET'])]
    public function getResellerUser(Request $request, $id, $userId): JsonResponse
    {
        try {
            $user = null;
            $reseller = $this->resellerService->getById($id, $this->getUser());
            if ($reseller) {
                $this->denyAccessUnlessGranted(Permission::RESELLER_USER_LIST, $reseller);
                $this->denyAccessUnlessGranted(null, $reseller->getTeam());
                $user = $this->resellerService->getResellerUser($userId, $reseller);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($user, array_merge(
            User::DEFAULT_DISPLAY_VALUES,
            [
                'blockingMessage',
                'isBlocked',
            ]
        ));
    }

    #[Route('/reseller/{id}/users/{userId}', requirements: ['id' => '\d+', 'userId' => '\d+'], methods: ['DELETE'])]
    public function deleteResellerUser(Request $request, $id, $userId, EntityManager $em): JsonResponse
    {
        try {
            $reseller = $this->resellerService->getById($id, $this->getUser());
            $user = $em->getRepository(User::class)->find($userId);
            if ($reseller && $user) {
                $this->denyAccessUnlessGranted(Permission::RESELLER_USER_DELETE, $reseller);
                $this->denyAccessUnlessGranted(null, $reseller->getTeam());
                $this->denyAccessUnlessGranted(null, $user->getTeam());
                $this->userService->deleteUserById($userId);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem('', [], Response::HTTP_NO_CONTENT);
    }

    #[Route('/reseller/{id}/users/{type}', requirements: ['type' => 'json|csv'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function resellerUsersList(Request $request, $id, $type): JsonResponse|CsvResponse
    {
        $reseller = $this->resellerService->getById($id, $this->getUser());
        $users = [];
        if ($reseller) {
            $this->denyAccessUnlessGranted(Permission::RESELLER_USER_LIST, $reseller);
            $this->denyAccessUnlessGranted(null, $reseller->getTeam());
            try {
                switch ($type) {
                    case 'json':
                        $users = $this->userService->resellerUserList($request->query->all(), $reseller);

                        return $this->viewItem($users);
                    case 'csv':
                        $users = $this->userService->resellerUserList($request->query->all(), $reseller, false);

                        return new CsvResponse($users);
                }
            } catch (\Exception $ex) {
                return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
            }
        }
    }

    #[Route('/reseller-notes/{id}/{type}', methods: ['GET'])]
    public function resellerNotes($id, $type)
    {
        $reseller = $this->resellerService->getById($id);
        $this->denyAccessUnlessGranted(null, $reseller->getTeam());
        $this->denyAccessUnlessGranted(Permission::RESELLER_NOTES_HISTORY, $reseller);

        try {
            if ($this->getUser()->isInAdminTeam()) {
                $notesList = $this->noteService->list($reseller, $type);
            } elseif (
                $this->getUser()->isInResellerTeam()
                && ($this->noteService->prepareNoteType($type) === Note::TYPE_RESELLER)) {
                $notesList = $this->noteService->list($reseller, $type);
            } else {
                throw new AccessDeniedException($this->translator->trans('general.access_denied'));
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItemsArray($notesList);
    }

    #[Route('/reseller/{id}/history/status', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function resellerHistoryStatusList(Request $request, $id): JsonResponse
    {
        $reseller = $this->resellerService->getById($id);
        $this->denyAccessUnlessGranted(null, $reseller->getTeam());

        try {
            $statusHistoryList = $this->entityHistoryService->list(
                Reseller::class,
                $reseller->getId(),
                EntityHistoryTypes::RESELLER_STATUS
            );
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItemsArray($statusHistoryList);
    }

    #[Route('/reseller/{id}/clients/{type}', requirements: ['type' => 'json|csv'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function resellerClientList(Request $request, $id, $type)
    {
        $this->denyAccessUnlessGranted(Permission::CLIENT_LIST, Client::class);
        $reseller = $this->resellerService->getById($id);
        $this->denyAccessUnlessGranted(null, $reseller->getTeam());
        $params = $request->query->all();
        try {
            switch ($type) {
                case 'json':
                    $clients = $this->clientService->getResellerClients($reseller, $params);

                    return new JsonResponse($clients);
                case 'csv':
                    $clients = $this->clientService->getResellerClients($reseller, $params, false);
                    $clients = $this->clientService->prepareExportData($clients, $params);

                    return new CsvResponse($clients);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/reseller/{id}/platform-settings', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function setPlatformSettings(Request $request, $id): JsonResponse
    {
        try {
            $reseller = $this->resellerService->getById($id);
            $platformSetting = null;
            if ($reseller) {
                $this->denyAccessUnlessGranted(Permission::RESELLER_EDIT, $reseller);
                $this->denyAccessUnlessGranted(Permission::PLATFORM_SETTING_RESELLER_EDIT, $this->getUser());
                $this->denyAccessUnlessGranted(null, $reseller->getTeam());

                $data = $request->request->all();
                $data['logo'] = $request->files->get('logo') ?? $request->request->get('logo');
                $data['favicon'] = $request->files->get('favicon') ?? $request->request->get('favicon');

                $platformSetting = $this->platformSettingService
                    ->setByTeam($data, $this->getUser(), $reseller->getTeam());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($platformSetting, PlatformSetting::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/reseller/{id}/platform-settings', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getPlatformSettings(Request $request, $id): JsonResponse
    {
        try {
            $reseller = $this->resellerService->getById($id);
            $platformSetting = null;
            if ($reseller) {
                $this->denyAccessUnlessGranted(null, $reseller->getTeam());
                $platformSetting = $reseller->getTeam()->getPlatformSettingByTeam();
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($platformSetting, PlatformSetting::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/platform-settings/domain', methods: ['GET'])]
    public function getPlatformSettingsByDomain(Request $request): JsonResponse
    {
        try {
            $domain = $request->query->get('domain');
            $platformSetting = $this->platformSettingService->getByDomain($domain);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($platformSetting, array_merge(PlatformSetting::DEFAULT_DISPLAY_VALUES, ['isChevron']));
    }

    #[Route('/reseller/{id}/notes', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function createClientNotes(Request $request, $id)
    {
        try {
            $reseller = $this->resellerService->getById($id);
            if ($reseller) {
                $this->denyAccessUnlessGranted(null, $reseller->getTeam());

                $this->resellerService->handleResellerNotes($request->request->all(), $reseller, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($reseller);
    }

    #[Route('/reseller/{id}/devices/{type}', requirements: ['type' => 'json|csv', 'id' => '\d+'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function deviceList(Request $request, $id, $type, DeviceService $deviceService)
    {
        try {
            $params = $request->query->all();

            $reseller = $this->resellerService->getById($id);
            if ($reseller) {
                $this->denyAccessUnlessGranted(null, $reseller->getTeam());

                switch ($type) {
                    case 'json':
                        $params['fields'] = array_merge(Device::DEFAULT_DISPLAY_VALUES,
                            ['model.name', 'vendor.name', 'addedToTeam', 'deactivatedAt', 'plan', 'team.chevronAccountId']);
                        $devices = $deviceService->resellerDeviceList($params, $this->getUser(), $reseller);

                        return $this->viewItem($devices);
                    case 'csv':
                        $devices = $deviceService->getResellerDeviceListExportData(
                            $params, $this->getUser(), $reseller
                        );

                        return new CsvResponse($devices);
                }
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }
}
