<?php

namespace App\Listener;

use App\Exceptions\SSOException;
use Monolog\Logger;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;
use PhpAmqpLib\Exception\AMQPIOException;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExceptionListener
{
    private function handleHttpException(\Throwable $exception): Response
    {
        $response = new JsonResponse();
        $response->setStatusCode($exception->getStatusCode());
        $response->headers->replace($exception->getHeaders());
        if ($exception->getPrevious()) {
            $exceptionForName = $exception->getPrevious();
        } else {
            $exceptionForName = $exception;
        }
        $this->data = [
            'errors' => [
                [
                    'code' => $response->getStatusCode(),
                    'title' => (new \ReflectionClass($exceptionForName))->getShortName(),
                    'detail' => $this->translator->trans($exception->getMessage()),
                    'error_code' => $exception->getMessage(),
                ]
            ]
        ];
        $response->setData($this->data);

        return $response;
    }

    private function handleSSOException(\Throwable $exception): Response
    {
        $response = new JsonResponse();
        $statusCode = $exception->getCode() != 0 ? $exception->getCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
        $response->setStatusCode($statusCode);
        $this->data = [
            'code' => $response->getStatusCode(),
            'title' => 'SSO Error',
            'message' => $exception->getMessage(),
        ];
        // @todo uncomment for staging?
//        $this->handleDebugSection($exception, $response);
        $response->setData($this->data);

        return $response;
    }

    private function handleAccessDeniedException(\Throwable $exception): Response
    {
        $response = new JsonResponse();
        $response->setStatusCode($exception->getCode());
        $this->data = [
            'code' => $response->getStatusCode(),
            'title' => 'AccessDenied',
            'detail' => $exception->getMessage(),
        ];
        $this->handleDebugSection($exception, $response);
        $response->setData($this->data);

        return $response;
    }

    private function handleDefaultException(\Throwable $exception): Response
    {
        $response = new JsonResponse();
        $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->data = [
            'code' => $response->getStatusCode(),
            'title' => 'InternalServerError',
            'detail' => 'Something went wrong',
        ];
        $this->handleDebugSection($exception, $response);
        $response->setData($this->data);

        return $response;
    }

    private function handleDebugSection(\Throwable $exception, Response $response)
    {
        if ($this->debug) {
            $classname = (new \ReflectionClass($exception))->getShortName();
            $this->data['debug'] = [
                'title' => $classname,
                'detail' => $exception->getMessage(),
                'backtrace' => $exception->getTrace(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'errors' => [
                    [
                        'code' => $response->getStatusCode(),
                        'title' => $classname,
                        'detail' => $exception->getMessage()
                    ]
                ]
            ];
        }
    }

    /**
     * Logs an exception.
     *
     * @param ExceptionEvent $event
     * @param \Throwable $exception The \Throwable instance
     */
    protected function logException(ExceptionEvent $event, \Throwable $exception)
    {
        $request = [
            'url' => $event->getRequest()->getUri(),
            'post_params' => $event->getRequest()->request->all(),
            'get_params' => $event->getRequest()->query->all(),
            'auth' => $event->getRequest()->headers->get('authorization')
        ];
        $message = sprintf(
            'Uncaught PHP Exception %s: "%s" at %s line %s',
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );

        if (null !== $this->logger) {
            if (!$exception instanceof HttpExceptionInterface || $exception->getStatusCode() >= 500) {
                $this->logger->critical($message, array('exception' => $exception, 'request' => $request));
            } else {
                $this->logger->error($message, array('exception' => $exception, 'request' => $request));
            }
        }
    }

    public function __construct(
        private TranslatorInterface $translator,
        private Logger              $logger,
        private bool                $debug = false,
        private array               $data = [],
    ) {
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        $this->logException($event, $exception);
        // Customize your response object to display the exception details
        // HttpExceptionInterface is a special type of exception that holds status code and header details

        $response = match (true) {
            $exception instanceof SSOException => $this->handleSSOException($exception),
            $exception instanceof HttpExceptionInterface => $this->handleHttpException($exception),
            $exception instanceof AccessDeniedException => $this->handleAccessDeniedException($exception),
            $exception instanceof AMQPConnectionClosedException => new JsonResponse(),
            $exception instanceof AMQPRuntimeException => new JsonResponse(),
            $exception instanceof AMQPIOException => new JsonResponse(),
            default => $this->handleDefaultException($exception),
        };

        // Send the modified response object to the event
        $event->setResponse($response);
    }
}
