<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Permission;
use App\Entity\Reseller;
use App\Entity\Team;
use App\Entity\User;
use App\Events\User\UserCreatedEvent;
use App\Mailer\MailSender;
use App\Mailer\Render\RenderedEmail;
use App\Response\CsvResponse;
use App\Service\Chat\ChatServiceInterface;
use App\Service\Client\ClientService;
use App\Service\Client\ClientUserService;
use App\Service\User\UserService;
use App\Service\User\VerificationService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserController extends BaseController
{
    private $clientService;
    private $userService;
    private $verificationService;
    private $translator;
    private $eventDispatcher;
    private $mailSender;
    private ClientUserService $clientUserService;
    private EntityManager $em;
    private $paginator;

    public function __construct(
        ClientService $clientService,
        ClientUserService $clientUserService,
        UserService $userService,
        VerificationService $verificationService,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        MailSender $mailSender,
        EntityManager $em,
        PaginatorInterface $paginator
    ) {
        $this->clientService = $clientService;
        $this->clientUserService = $clientUserService;
        $this->userService = $userService;
        $this->verificationService = $verificationService;
        $this->translator = $translator;
        $this->eventDispatcher = $eventDispatcher;
        $this->mailSender = $mailSender;
        $this->em = $em;
        $this->paginator = $paginator;
    }

    #[Route('/clients/{clientId}/users/{userId}', requirements: ['clientId' => '\d+', 'userId' => '\d+'], methods: ['GET'])]
    public function clientUser(Request $request, $clientId, $userId): JsonResponse
    {
        $fields = $request->query->get('fields') ?? [];

        try {
            $user = $this->clientUserService->getClientUser($clientId, $userId);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
        return $this->viewItem($user, array_merge(User::DEFAULT_DISPLAY_VALUES, ['client', 'updatedBy'], $fields));
    }

    #[Route('/users/admin', methods: ['POST'])]
    public function createAdminTeamUser(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::ADMIN_TEAM_NEW_USER, User::class);
        try {
            $user = $this->userService->create(
                array_merge(
                    $request->request->all(),
                    ['teamType' => Team::TEAM_ADMIN, 'createdBy' => $this->getUser()]
                )
            );
            $this->eventDispatcher->dispatch(new UserCreatedEvent($user), UserCreatedEvent::NAME);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($user, array_merge(User::DEFAULT_DISPLAY_VALUES, ['teamPermission']));
    }

    #[Route('/clients/{clientId}/users/{userId}', requirements: ['clientId' => '\d+', 'userId' => '\d+'], methods: ['POST'])]
    public function updateClientUser(Request $request, $clientId, $userId): JsonResponse
    {
        $client = $this->em->getRepository(Client::class)->find($clientId);
        if ($this->getUser()->getId() !== $userId) {
            $this->denyAccessUnlessGranted(Permission::CLIENT_EDIT_USER, $client);
        }

        try {
            $avatarFile = $request->files->get('avatar') ?? null;
            $user = $this->clientUserService->editClientUser(
                $clientId,
                $userId,
                $this->getUser(),
                array_merge($request->request->all(), ['avatar' => $avatarFile])
            );
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem(
            $user,
            array_merge(
                User::DEFAULT_DISPLAY_VALUES,
                [
                    'updatedAt',
                    'blockingMessage',
                    'isBlocked',
                ]
            )
        );
    }

    #[Route('/admin/users', methods: ['GET'])]
    public function adminUserList(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::ADMIN_TEAM_USER_LIST, User::class);
        try {
            $users = $this->userService->usersList($request->query->all());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($users);
    }

    #[Route('/clients/{clientId}/users/{userId}', requirements: ['clientId' => '\d+', 'userId' => '\d+'], methods: ['DELETE'])]
    public function deleteClientUser($clientId, $userId)
    {
        $client = $this->em->getRepository(Client::class)->find($clientId);
        $this->denyAccessUnlessGranted(Permission::CLIENT_DELETE_USER, $client);

        try {
            $this->clientUserService->deleteClientUser($clientId, $userId);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem('', [], Response::HTTP_NO_CONTENT);
    }

    #[Route('/admin/users/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getAdminUserById(Request $request, $id): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::ADMIN_TEAM_USER_LIST, User::class);

        try {
            $users = $this->userService->getAdminUserById($id);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem(
            $users,
            array_merge(
                User::DEFAULT_DISPLAY_VALUES,
                [
                    'updatedBy',
                    'updatedAt',
                    'blockingMessage',
                    'teamPermission',
                    'managedTeams',
                    'isBlocked',
                    'allTeamsPermissions'
                ]
            )
        );
    }

    #[Route('/admin/users/{id}', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function updateAdminUserById(Request $request, $id): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::ADMIN_TEAM_EDIT_USER, User::class);

        try {
            $avatarFile = $request->files->get('avatar') ?? null;
            $user = $this->userService->editAdminTeamUser(
                $id,
                array_merge($request->request->all(), ['avatar' => $avatarFile]),
                $this->getUser()
            );
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem(
            $user,
            array_merge(
                User::DEFAULT_DISPLAY_VALUES,
                [
                    'updatedBy',
                    'updatedAt',
                    'blockingMessage',
                    'isBlocked',
                    'teamPermission',
                    'allTeamsPermissions'
                ]
            )
        );
    }

    #[Route('/admin/users/{managerId}/teams', requirements: ['managerId' => '\d+'], methods: ['PATCH'])]
    public function addAdminUserTeamsPermissions(Request $request, $managerId): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::ADMIN_TEAM_EDIT_USER, User::class);

        try {
            $teamsData = $request->request->all()['teams'];
            $user = $this->userService->addTeamsToManager($managerId, $teamsData);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem(
            $user,
            array_merge(
                User::DEFAULT_DISPLAY_VALUES,
                ['blockingMessage', 'isBlocked', 'teamPermission', 'allTeamsPermissions']
            )
        );
    }

    #[Route('/admin/users/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function deleteAdminUserById(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(Permission::ADMIN_TEAM_DELETE_USER, User::class);

        try {
            if ($this->getUser()->getId() === $id) {
                throw new \Exception($this->translator->trans('entities.user.delete_yourself'));
            }
            $this->userService->deleteUserById($id);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem('', [], Response::HTTP_NO_CONTENT);
    }

    #[Route('/users/csv', methods: ['GET'])]
    public function exportCsvUserList(Request $request)
    {
        try {
            $params = $request->query->all();
            $users = [];
            if ($this->getUser()->isInAdminTeam()) {
                if ($params['teamId'] ?? null) {
                    $users = $this->clientUserService->getClientUsers($params, false);
                } else {
                    $users = $this->userService->usersList($params, false);
                }
            } elseif ($this->getUser()->isInClientTeam()) {
                $params['client_id'] = $this->getUser()->getClientId();
                $users = $this->clientUserService->getClientUsers($params, false);
            }

            $users = $this->userService->prepareUserListExportData($users, $params);

            $response = new CsvResponse($users);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $response;
    }

    #[Route('/users/check-verify-token', methods: ['POST'])]
    public function checkVerifyTokenAction(Request $request)
    {
        try {
            return $this->viewItem(
                [
                    'tokenValid' => $this->verificationService->isVerifyTokenValid($request->request->get('token')),
                ]
            );
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/users/{id}/restore', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function restoreUser($id)
    {
        $this->denyAccessUnlessGranted(Permission::CLIENT_ARCHIVE_USER, Client::class);
        $this->denyAccessUnlessGranted(null, $this->userService->getUserById($id)->getTeam());
        try {
            $user = $this->userService->restore($id, $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($user, User::DISPLAYED_VALUES);
    }

    #[Route('/users/{id}/undelete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function undeleteUser($id)
    {
        $this->denyAccessUnlessGranted(Permission::CLIENT_DELETE_USER, Client::class);
        $this->denyAccessUnlessGranted(null, $this->userService->getUserById($id)->getTeam());
        try {
            $user = $this->userService->undelete($id, $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($user, User::DISPLAYED_VALUES);
    }

    #[Route('/users/{id}/archive', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function archiveUser($id)
    {
        $this->denyAccessUnlessGranted(Permission::CLIENT_ARCHIVE_USER, Client::class);
        $this->denyAccessUnlessGranted(null, $this->userService->getUserById($id)->getTeam());
        try {
            $user = $this->userService->archiveUserById($id);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($user, User::DISPLAYED_VALUES);
    }

    #[Route('drivers', methods: ['GET'])]
    public function driverList(Request $request): JsonResponse
    {
//        $this->denyAccessUnlessGranted(Permission::DRIVER_LIST, User::class);
        try {
            $users = $this->userService->getDrivers($request->query->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($users);
    }

    #[Route('/drivers/dropdown', methods: ['GET'])]
    public function driverListDropdown(Request $request): JsonResponse
    {
        try {
            $users = $this->userService->getDrivers($request->query->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($users);
    }

    #[Route('/support/request', methods: ['POST'])]
    public function supportRequest(Request $request)
    {
        $body = $request->request->get('body');
        $replyTo = $request->request->get('email') ?? null;
        $subject = $request->request->get('subject') ?? 'Support request';
        $files = $request->files->all('files') ?? [];

        $message = new RenderedEmail(
            $subject,
            $body
        );

        $sendTo = $this->getUser()->getSupportEmail() ?? $this->getParameter('support_mail');
        $this->mailSender->sendEmail([$sendTo], $message, null, $files, $replyTo);

        return $this->viewItem('', [], Response::HTTP_NO_CONTENT);
    }

    #[Route('/client/users', methods: ['GET'])]
    public function clientUsersList(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::ADMIN_TEAM_USER_LIST, User::class);
        try {
            $params = $request->query->all();
            $params['teamType'] = Team::TEAM_CLIENT;

            $users = $this->userService->usersList($params);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($users);
    }

    #[Route('/users/filter/{type}', requirements: ['type' => 'all|arc|ar|rc|c'], defaults: ['type' => 'all'], methods: ['GET'])]
    public function getUsersForFilter(Request $request, string $type)
    {
        $currentUser = $this->getUser();
        if ($type === 'rc') {
            $this->denyAccessUnlessGranted(Permission::RESELLER_USER_LIST, Reseller::class);
            $this->denyAccessUnlessGranted(null, $currentUser->getTeam());
        } elseif ($type === 'c') {
            $this->denyAccessUnlessGranted(null, $currentUser->getTeam());
        } else {
            $this->denyAccessUnlessGranted(Permission::ADMIN_TEAM_USER_LIST, User::class);
        }

        try {
            $users = $this->userService->getUsersForFilter($type, $request->query->all(), $currentUser);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($users);
    }

    #[Route('/teams/filter/{type}', requirements: ['type' => 'all|arc|ar|rc|c'], defaults: ['type' => 'all'], methods: ['GET'])]
    public function getTeamsForFilter(Request $request, string $type)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        try {
            $teams = $this->clientService->getTeamsForFilter($type, $request->query->all(), $this->getUser());
            $pagination = $this->paginator->paginate($teams, $page, $limit);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($pagination);
    }

    #[Route('/clients/filter/{type}', requirements: ['type' => 'all|arc|ar|rc|c'], defaults: ['type' => 'all'], methods: ['GET'])]
    public function getTeamsForFilterPrev(Request $request, string $type)
    {
        //TODO: remove after update prod
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        try {
            $teams = $this->clientService->getTeamsForFilter($type, $request->query->all(), $this->getUser());
            $pagination = $this->paginator->paginate($teams, $page, $limit);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($pagination);
    }

    #[Route('/users/online', methods: ['POST'])]
    public function online(Request $request, ChatServiceInterface $chatService, EntityManagerInterface $em)
    {
        try {
            $user = $this->getUser();
            $prevUserNetworkStatus = $user->getNetworkStatus();
            $user = $this->userService->updateUserNetworkStatus($user);

            if ($prevUserNetworkStatus == User::NETWORK_STATUS_OFFLINE) {
                $chatService->notifyTeamUserStatusUpdated($user);
            }
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($user, array_merge(User::SIMPLE_VALUES_CHAT, ['lastOnlineDate']));
    }
}
