<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\ChatHistory;
use App\Entity\Permission;
use App\Form\ChatHistoryType;
use App\Form\ChatType;
use App\Service\Chat\CentrifugoService;
use App\Service\Chat\ChatService;
use App\Service\File\FileServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/chats')]
class ChatController extends BaseController
{
    private ChatService $chatService;
    private TranslatorInterface $translator;

    public function __construct(
        CentrifugoService $centrifugoService,
        TranslatorInterface $translator
    ) {
        $this->chatService = $centrifugoService;
        $this->translator = $translator;
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request)
    {
        try {
            $this->denyAccessUnlessGranted(Permission::CHAT_CREATE, Chat::class);
            $params = $request->request->all();
            $this->validateRequestInput(ChatType::class, new Chat(), $params);
            $chat = $this->chatService->createChat($params, $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($chat);
    }

    #[Route('', requirements: ['type' => 'json|csv'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function list(Request $request, string $type)
    {
        try {
            $this->denyAccessUnlessGranted(Permission::CHAT_LIST, Chat::class);
            $params = $request->query->all();
            $chatList = $this->chatService->listChat($params, $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($chatList);
    }

    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function view(Request $request, int $id)
    {
        try {
            $chat = $this->chatService->getById($id);

            if (!$chat) {
                throw new NotFoundHttpException($this->translator->trans('entities.chat.id_does_not_exist', [
                    '%id%' => $id
                ]));
            }

            $this->denyAccessUnlessGranted(null, $chat->getTeam());
            $this->denyAccessUnlessGranted(Permission::CHAT_LIST, $chat);
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($chat, [], 200, null, $this->getUser());
    }

    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(Request $request, int $id)
    {
        try {
            $chat = $this->chatService->getById($id);

            if (!$chat) {
                throw new NotFoundHttpException($this->translator->trans('entities.chat.id_does_not_exist', [
                    '%id%' => $id
                ]));
            }

            $this->denyAccessUnlessGranted(null, $chat->getTeam());
            $this->denyAccessUnlessGranted(Permission::CHAT_LIST, $chat);
            $chat = $this->chatService->deleteChat($chat, $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($chat);
    }

    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function edit(Request $request, int $id)
    {
        try {
            $params = $request->request->all();
            $chat = $this->chatService->getById($id);

            if (!$chat) {
                throw new NotFoundHttpException($this->translator->trans('entities.chat.id_does_not_exist', [
                    '%id%' => $id
                ]));
            }

            $this->denyAccessUnlessGranted(null, $chat->getTeam());
            $this->denyAccessUnlessGranted(Permission::CHAT_LIST, $chat);
            $chat = $this->chatService->editChat($chat, $params, $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($chat);
    }

    #[Route('/{id}/users', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function editUsers(Request $request, int $id)
    {
        try {
            $params = $request->request->all();
            $chat = $this->chatService->getById($id);

            if (!$chat) {
                throw new NotFoundHttpException($this->translator->trans('entities.chat.id_does_not_exist', [
                    '%id%' => $id
                ]));
            }

            $this->denyAccessUnlessGranted(null, $chat->getTeam());
            $this->denyAccessUnlessGranted(Permission::CHAT_LIST, $chat);
            $chat = $this->chatService->editChatUsers($chat, $params, $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($chat);
    }

    #[Route('/{id}/history', requirements: [
        'type' => 'json|csv',
        'id' => '\d+'
    ], defaults: ['type' => 'json'], methods: ['GET'])]
    public function history(Request $request, int $id, string $type)
    {
        try {
            $chat = $this->chatService->getById($id);

            if (!$chat) {
                throw new NotFoundHttpException($this->translator->trans('entities.chat.id_does_not_exist', [
                    '%id%' => $id
                ]));
            }

            $this->denyAccessUnlessGranted(null, $chat->getTeam());
            $chatHistory = $this->chatService->getChatHistory($chat, $request, $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($chatHistory);
    }

    #[Route('/{id}/message', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function sendChatMessage(Request $request, int $id)
    {
        try {
            $params = array_merge($request->request->all(), $request->files->all());
            $this->validateRequestInput(ChatHistoryType::class, new ChatHistory(), $params);
            $chat = $this->chatService->getById($id);

            if (!$chat) {
                throw new NotFoundHttpException($this->translator->trans('entities.chat.id_does_not_exist', [
                    '%id%' => $id
                ]));
            }

            $this->denyAccessUnlessGranted(null, $chat->getTeam());
            $this->denyAccessUnlessGranted(Permission::CHAT_LIST, $chat);
            $chatMessage = $this->chatService->createChatMessage($chat, $params, $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($chatMessage, [], 200, null, $this->getUser());
    }

    #[Route('/{chatId}/message/{messageId}', requirements: [
        'messageId' => '\d+',
        'chatId' => '\d+'
    ], methods: ['PATCH'])]
    public function editChatMessage(Request $request, int $chatId, int $messageId)
    {
        try {
            $params = $request->request->all();
            $chatMessage = $this->chatService->getChatMessageById($messageId);

            if (!$chatMessage) {
                throw new NotFoundHttpException($this->translator->trans('entities.chat_history.id_does_not_exist', [
                    '%id%' => $messageId
                ]));
            }
            $chat = $this->chatService->getById($chatId);

            if (!$chat) {
                throw new NotFoundHttpException($this->translator->trans('entities.chat.id_does_not_exist', [
                    '%id%' => $chatId
                ]));
            }

            $this->denyAccessUnlessGranted(null, $chat->getTeam());
            $this->denyAccessUnlessGranted(Permission::CHAT_LIST, $chat);
            $chat = $this->chatService->editChatMessage($chatMessage, $params, $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($chatMessage, [], 200, null, $this->getUser());
    }

    #[Route('/{chatId}/message/{messageId}', requirements: [
        'messageId' => '\d+',
        'chatId' => '\d+'
    ], methods: ['DELETE'])]
    public function deleteChatMessage(Request $request, int $chatId, int $messageId)
    {
        try {
            $chatMessage = $this->chatService->getChatMessageById($messageId);

            if (!$chatMessage) {
                throw new NotFoundHttpException($this->translator->trans('entities.chat_history.id_does_not_exist', [
                    '%id%' => $messageId
                ]));
            }

            $chat = $this->chatService->getById($chatId);

            if (!$chat) {
                throw new NotFoundHttpException($this->translator->trans('entities.chat.id_does_not_exist', [
                    '%id%' => $chatId
                ]));
            }

            $this->denyAccessUnlessGranted(null, $chat->getTeam());
            $this->denyAccessUnlessGranted(Permission::CHAT_LIST, $chat);
            $chatMessage = $this->chatService->deleteChatMessage($chatMessage, $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($chatMessage);
    }

    #[Route('/user-channels', methods: ['GET'])]
    public function userChannels(Request $request)
    {
        try {
            $this->denyAccessUnlessGranted(Permission::CHAT_LIST, Chat::class);
            $privateChannel = $this->chatService->getPrivateChannel($this->getUser());
            $teamChannel = $this->chatService->getTeamChannel($this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem([
            'private' => $privateChannel,
            'team' => $teamChannel,
        ]);
    }

    #[Route('/{chatId}/file/{fileId}/{filename}', name: 'chat_attachment_source', requirements: [
        'chatId' => '\d+',
        'fileId' => '\d+'
    ], defaults: ['filename' => 'image.png'], methods: ['GET'])]
    public function getChatAttachmentSource(
        Request $request,
        int $chatId,
        int $fileId,
        string $filename,
        FileServiceInterface $fileService
    ) {
        try {
            $file = $fileService->getById($fileId);

            if (!$file) {
                throw new NotFoundHttpException(
                    $this->translator->trans('entities.chat_history.file_id_does_not_exist', ['%id%' => $fileId])
                );
            }

            $chat = $this->chatService->getById($chatId);

            if (!$chat) {
                throw new NotFoundHttpException($this->translator->trans('entities.chat.id_does_not_exist', [
                    '%id%' => $chatId
                ]));
            }

            $this->denyAccessUnlessGranted(null, $chat->getTeam());
            $this->denyAccessUnlessGranted(Permission::CHAT_LIST, $chat);

            return $fileService->fetchSource($file);
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/publish', methods: ['POST'])]
    public function publish(Request $request)
    {
        try {
            $params = $request->request->all();
            $result = $this->chatService->publish($params);
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($result);
    }

    #[Route('/{chatId}/message/{messageId}/file/{fileId}', requirements: [
        'messageId' => '\d+',
        'chatId' => '\d+',
        'fileId' => '\d+'
    ], methods: ['DELETE'])]
    public function deleteChatMessageAttachment(
        Request $request,
        int $chatId,
        int $messageId,
        int $fileId,
        FileServiceInterface $fileService
    ): JsonResponse|Response {
        try {
            $file = $fileService->getById($fileId);

            if (!$file) {
                throw new NotFoundHttpException(
                    $this->translator->trans('entities.chat_history.file_id_does_not_exist', ['%id%' => $fileId])
                );
            }

            $chatMessage = $this->chatService->getChatMessageById($messageId);

            if (!$chatMessage) {
                throw new NotFoundHttpException($this->translator->trans('entities.chat_history.id_does_not_exist', [
                    '%id%' => $messageId
                ]));
            }
            if (!$chatMessage->getFile()) {
                throw new NotFoundHttpException(
                    $this->translator->trans('entities.chat_history.attachment_does_not_exist', [
                        '%id%' => $messageId
                    ]));
            }
            if ($chatMessage->getFile()->getId() != $file->getId()) {
                throw new \InvalidArgumentException(
                    $this->translator->trans('entities.chat_history.attachment_does_not_equal_to_file', [
                        '%id%' => $messageId
                    ]));
            }

            $chat = $this->chatService->getById($chatId);

            if (!$chat) {
                throw new NotFoundHttpException($this->translator->trans('entities.chat.id_does_not_exist', [
                    '%id%' => $chatId
                ]));
            }

            $this->denyAccessUnlessGranted(null, $chat->getTeam());
            $this->denyAccessUnlessGranted(Permission::CHAT_LIST, $chat);
            $chat = $this->chatService->deleteChatMessageAttachment($chatMessage, $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($chat);
    }

    #[Route('/{chatId}/message/{messageId}/file', requirements: [
        'messageId' => '\d+',
        'chatId' => '\d+'
    ], methods: ['POST'])]
    public function addChatMessageAttachment(
        Request $request,
        int $chatId,
        int $messageId
    ): JsonResponse|Response {
        try {
            $params = array_merge($request->request->all(), $request->files->all());
            $chatMessage = $this->chatService->getChatMessageById($messageId);

            if (!$chatMessage) {
                throw new NotFoundHttpException($this->translator->trans('entities.chat_history.id_does_not_exist', [
                    '%id%' => $messageId
                ]));
            }
            if ($chatMessage->getFile()) {
                throw new NotFoundHttpException(
                    $this->translator->trans('entities.chat_history.attachment_already_exists', [
                        '%id%' => $messageId
                    ]));
            }

            $this->validateRequestInput(ChatHistoryType::class, $chatMessage, $params);
            $chat = $this->chatService->getById($chatId);

            if (!$chat) {
                throw new NotFoundHttpException($this->translator->trans('entities.chat.id_does_not_exist', [
                    '%id%' => $chatId
                ]));
            }

            $this->denyAccessUnlessGranted(null, $chat->getTeam());
            $this->denyAccessUnlessGranted(Permission::CHAT_LIST, $chat);
            $chat = $this->chatService->addChatMessageAttachment($chatMessage, $params, $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($chat);
    }

    #[Route('/message/unread', methods: ['GET'])]
    public function chatMessagesTotalUnread()
    {
        try {
            $this->denyAccessUnlessGranted(Permission::CHAT_LIST, Chat::class);
            $chatMessagesTotalUnread = $this->chatService->getChatMessagesTotalUnread($this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem(['totalUnreadCount' => $chatMessagesTotalUnread]);
    }
}
