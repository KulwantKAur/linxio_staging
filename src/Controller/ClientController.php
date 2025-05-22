<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Note;
use App\Entity\Permission;
use App\Entity\PlatformSetting;
use App\Enums\EntityHistoryTypes;
use App\Response\CsvResponse;
use App\Service\Client\ClientService;
use App\Service\Client\ClientUserService;
use App\Service\EntityHistory\EntityHistoryService;
use App\Service\Note\NoteService;
use App\Service\PlatformSetting\PlatformSettingService;
use App\Service\User\UserService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ClientController extends BaseController
{
    private $clientService;
    private $platformSettingService;
    private $noteService;
    private $translator;
    private $entityHistoryService;
    private $clientUserService;

    public function __construct(
        ClientService $clientService,
        ClientUserService $clientUserService,
        PlatformSettingService $platformSettingService,
        NoteService $noteService,
        TranslatorInterface $translator,
        EntityHistoryService $entityHistoryService
    ) {
        $this->clientService = $clientService;
        $this->clientUserService = $clientUserService;
        $this->platformSettingService = $platformSettingService;
        $this->noteService = $noteService;
        $this->translator = $translator;
        $this->entityHistoryService = $entityHistoryService;
    }

    #[Route('/clients', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted(Permission::NEW_CLIENT, Client::class);

            $avatarFile = $request->files->get('picture') ?? null;
            $client = $this->clientService->create(
                array_merge_recursive(
                    $request->request->all(),
                    [
                        'user' => ['avatar' => $avatarFile]
                    ]
                ), $this->getUser()
            );
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($client);
    }

    #[Route('/clients/{id}', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function edit(Request $request, $id): JsonResponse
    {
        $client = $this->clientService->getClientById($id);
        $this->denyAccessUnlessGranted(Permission::CONFIGURATION_COMPANY_INFO_EDIT, $client);
        $this->denyAccessUnlessGranted(null, $client->getTeam());

        try {
            $client = $this->clientService->edit(
                $id,
                array_merge_recursive(
                    $request->request->all(), ['updatedBy' => $this->getUser()]), $this->getUser()
            );
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($client);
    }

    #[Route('/clients/{type}', requirements: ['type' => 'json|csv'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function clientList(Request $request, $type)
    {
        $this->denyAccessUnlessGranted(Permission::CLIENT_LIST, Client::class);
        $params = $request->query->all();
        try {
            switch ($type) {
                case 'json':
                    $clients = $this->clientService->getClients($params, $this->getUser());

                    return new JsonResponse($clients);
                case 'csv':
                    $iterator = $this->clientService->getClients($params, $this->getUser(), false, true);
                    $data = [];

                    foreach ($iterator as $client) {
                        $data[] = $client->toExport($params['fields'] ?? []);
                    }

                    $clients = $this->clientService->translateEntityArrayForExport($data, $params['fields'] ?? [],
                        Client::class);

                    return new CsvResponse($clients);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/clients/{id}/users', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function registerClientUser(Request $request, $id): JsonResponse
    {
        $client = $this->clientService->getClientById($id);
        $this->denyAccessUnlessGranted(Permission::CLIENT_NEW_USER, $client);
        $this->denyAccessUnlessGranted(null, $client->getTeam());

        try {
            $client = $this->clientUserService->createClientUser(
                array_merge_recursive(
                    $request->request->all(),
                    [
                        'createdBy' => $this->getUser(),
                        'clientId' => $id
                    ]
                ),
                $this->getUser()
            );
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($client);
    }

    #[Route('/clients/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getClientById(Request $request, $id): JsonResponse
    {
        try {
            $client = $this->clientService->getClientById($id);
            if ($client) {
//                $this->denyAccessUnlessGranted(Permission::CONFIGURATION_COMPANY_INFO, $client);
                $this->denyAccessUnlessGranted(null, $client->getTeam());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem(
            $client,
            array_merge(Client::SIMPLE_DISPLAY_VALUES,
                ['manager', 'salesManager', 'keyContact', 'plan', 'updatedBy', 'teamWithIsChevron'])
        );
    }

    #[Route('/clients/{id}/users/{type}', requirements: [
        'id' => '\d+',
        'type' => 'json|csv'
    ], defaults: ['type' => 'json'], methods: ['GET'])]
    public function clientUsersList(Request $request, $id, $type, UserService $userService): JsonResponse|CsvResponse
    {
        $client = $this->clientService->getClientById($id);
        $this->denyAccessUnlessGranted(null, $client->getTeam());

        $params = $request->query->all();
        try {
            $params['client_id'] = $id;

            switch ($type) {
                case 'json':
                    $users = $this->clientUserService->getClientUsers($params);

                    return new JsonResponse($users);
                case 'csv':
                    $users = $this->clientUserService->getClientUsers($params, false);
                    $users = $userService->prepareUserListExportData($users, $params);

                    return new CsvResponse($users);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/clients/{id}/users/dropdown', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function clientUsersListDropdown(Request $request, $id): JsonResponse
    {
        $client = $this->clientService->getClientById($id);
        $this->denyAccessUnlessGranted(null, $client->getTeam());
        $params = $request->query->all();
        try {
            $params['client_id'] = $id;
            $clients = $this->clientUserService->getClientUsers($params);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
        return new JsonResponse($clients);
    }

    #[Route('/clients/{id}/users/chat', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function clientUsersListChat(
        Request $request,
        $id,
        TranslatorInterface $translator
    ): JsonResponse {
        $client = $this->clientService->getClientById($id);

        if (!$client) {
            throw new NotFoundHttpException($translator->trans('entities.client.not_found'));
        }

        $this->denyAccessUnlessGranted(null, $client->getTeam());
        $params = $request->query->all();

        try {
            $clientUsers = $this->clientUserService->getClientUsersForChat($id, $params);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($clientUsers);
    }

    #[Route('/clients/{id}/history/status', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function clientHistoryStatusList(Request $request, $id): JsonResponse
    {
        $client = $this->clientService->getClientById($id);
        $this->denyAccessUnlessGranted(Permission::CLIENT_STATUS_HISTORY, $client);
        $this->denyAccessUnlessGranted(null, $client->getTeam());

        try {
            $client = $this->clientService->get($id);
            $statusHistoryList = $this->entityHistoryService->list(
                Client::class,
                $client->getId(),
                EntityHistoryTypes::CLIENT_STATUS
            );
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItemsArray($statusHistoryList);
    }

    #[Route('/clients/{id}/history/contract', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function clientContractHistoryList(
        Request $request,
        $id,
        EntityHistoryService $entityHistoryService,
        PaginatorInterface $paginator
    ): JsonResponse {
        $client = $this->clientService->getClientById($id);
        $this->denyAccessUnlessGranted(null, $client->getTeam());
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        try {
            $history = $entityHistoryService
                ->listPagination(Client::class, $id, EntityHistoryTypes::CLIENT_CONTRACT_CHANGED);
            $pagination = $paginator->paginate($history, $page, $limit);
            $data = array_map(fn($item) => $item->toArray(), $pagination->getItems());
            $pagination->setItems($data);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($pagination);
    }

    #[Route('/clients/{id}/history/updated', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function clientHistoryUpdatedList(Request $request, $id): JsonResponse
    {
        $client = $this->clientService->getClientById($id);
        $this->denyAccessUnlessGranted(Permission::CLIENT_CREATED_HISTORY, $client);
        $this->denyAccessUnlessGranted(null, $client->getTeam());

        try {
            $client = $this->clientService->get($id);
            $statusHistoryList = $this->entityHistoryService->list(
                Client::class,
                $client->getId(),
                EntityHistoryTypes::CLIENT_UPDATED
            );
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItemsArray($statusHistoryList);
    }

    #[Route('/client-notes/{id}/{type}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function clientNotes($id, $type)
    {
        $client = $this->clientService->getClientById($id);
        $this->denyAccessUnlessGranted(Permission::CLIENT_NOTES_HISTORY, $client);
        $this->denyAccessUnlessGranted(null, $client->getTeam());

        try {
            $client = $this->clientService->get($id);
            if ($this->getUser()->isInAdminTeam() || $this->getUser()->isInResellerTeam()) {
                $notesList = $this->noteService->list($client, $type);
            } elseif (
                $this->getUser()->isInClientTeam()
                && ($this->noteService->prepareNoteType($type) === Note::TYPE_CLIENT)) {
                $notesList = $this->noteService->list($client, $type);
            } else {
                throw new AccessDeniedHttpException($this->translator->trans('general.access_denied'));
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItemsArray($notesList);
    }

    #[Route('/client/platform-settings', methods: ['GET'])]
    public function getPlatformSettings(Request $request): JsonResponse
    {
        try {
            $platformSetting = $this->getUser()->getTeam()->getPlatformSettingByTeam();
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($platformSetting, PlatformSetting::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/clients/{id}/notes', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function createClientNotes(Request $request, $id)
    {
        try {
            $client = $this->clientService->getClientById($id);
            if ($client) {
                $this->denyAccessUnlessGranted(Permission::CONFIGURATION_COMPANY_INFO_EDIT, $client);
                $this->denyAccessUnlessGranted(null, $client->getTeam());

                $this->clientService->handleNotesFields($request->request->all(), $client, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($client);
    }
}