<?php

namespace App\Controller;

use App\Entity\BaseEntity;
use App\Entity\User;
use App\Exceptions\ValidationException;
use App\Kernel;
use Elasticsearch\Common\Exceptions\RuntimeException;
use Knp\Component\Pager\Pagination\AbstractPagination;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use App\Service\Traccar\Model\TraccarModel;

class BaseController extends AbstractController
{
    public const ADDITIONAL_FIELDS = 'additionalFields';

    public static function viewItem(
        $entity,
        array $include = [],
        int $code = 200,
        ?array $additionalFields = null,
        ?User $user = null
    ): JsonResponse {
        $result = $entity;

        if ($entity instanceof BaseEntity) {
            if ($user) {
                $result = $result->toArray($include, $user);
            } else {
                $result = $result->toArray($include);
            }
        }
        if ($entity instanceof AbstractPagination) {
            $result = self::viewItemsFromPagination($entity, $additionalFields);
        }
        if ($entity instanceof TraccarModel) {
            $result = json_decode(json_encode($entity), true);
        }

        return new JsonResponse($result, $code);
    }

    protected function viewItemsArray($entities, array $include = [], int $code = 200, ?User $user = null): JsonResponse
    {
        $result = [];
        if (!$entities) {
            return new JsonResponse($result, $code);
        }

        foreach ($entities as $entity) {
            if ($entity instanceof BaseEntity) {
                if ($user) {
                    $result[] = $entity->toArray($include, $user);
                } else {
                    $result[] = $entity->toArray($include);
                }
            } else {
                $result[] = $entity;
            }
        }

        return new JsonResponse($result, $code);
    }

    protected function viewError($messages, int $code = 500, string $title = 'Exception'): JsonResponse
    {
        if ((getenv('SYMFONY_ENV') === 'prod' || getenv('SYMFONY_ENV') === 'stage') && !is_array($messages)) {
            throw new \Exception($messages, $code);
        }

        return new JsonResponse(
            [
                'errors' => [
                    [
                        'code' => $code,
                        'title' => $title,
                        'detail' => $messages,
                    ]
                ]
            ],
            $code
        );
    }

    protected function viewException(\Throwable $exception, int $code = 500): JsonResponse
    {
        if ($exception instanceof ValidationException) {
            return $this->viewError($exception->getErrors(), $code, 'ValidationException');
        }

        if ($exception instanceof AuthenticationException) {
            return $this->viewError($exception->getMessageKey(), $code, 'ValidationException');
        }

        return $this->viewError($exception->getMessage(), $code);
    }

    protected function viewJsonExceptionError(
        \Throwable $exception,
        int $code = Response::HTTP_BAD_REQUEST
    ): JsonResponse {
        $code = array_key_exists($code, Response::$statusTexts) ? $code : Response::HTTP_BAD_REQUEST;

        return new JsonResponse(
            [
                'errors' => [
                    [
                        'code' => $code,
                        'title' => 'Exception',
                        'detail' => $exception->getMessage(),
                        'trace' => $exception->getTraceAsString(),
                    ]
                ]
            ],
            $code
        );
    }

    protected function viewJsonError(string $message, int $code = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return new JsonResponse(
            [
                'errors' => [
                    [
                        'code' => $code,
                        'title' => 'Exception',
                        'detail' => $message,
                    ]
                ]
            ],
            $code
        );
    }

    public static function viewItemsFromPagination(
        AbstractPagination $pagination,
        ?array $additionalFields = null
    ): array {
        $result = [
            'page' => $pagination->getCurrentPageNumber(),
            'limit' => $pagination->getItemNumberPerPage(),
            'total' => $pagination->getTotalItemCount(),
            'data' => $pagination->getItems()
        ];

        if ($additionalFields) {
            $result[self::ADDITIONAL_FIELDS] = $additionalFields;
        }

        return $result;
    }

    public function validateRequestInput(string $formClass, $entity, array $data): void
    {
        $errors = [];
        $formErrors = [];
        $form = $this->createForm($formClass, $entity);
        $form->submit($data);

        if (!$form->isValid()) {
            $formErrors = $form->getErrors(true);
        }

        /** @var FormError $error */
        foreach ($formErrors as $error) {
            $property = $error->getOrigin() ? $error->getOrigin()->getName() : $formClass;
            $errors[$property] = $error->getMessage();
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }
}