<?php

namespace App\Listener;

use Monolog\Logger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/*
 * @todo remove this class if new ExceptionListener is ok
 */
class ExceptionListenerOld
{
    private $translator;
    /**
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * @var bool
     */
    private $debug;

    public function __construct($translator, Logger $logger, bool $debug = false)
    {
        $this->translator = $translator;
        $this->logger = $logger;
        $this->debug = $debug;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        $req = [
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
        $this->logException($exception, $message, $req);

        // Customize your response object to display the exception details
        $response = new JsonResponse();
        // HttpExceptionInterface is a special type of exception that
        // holds status code and header details
        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            $data = [
                'code' => $response->getStatusCode(),
                'title' => 'InternalServerError',
                'detail' => 'Something went wrong with SSO',
            ];
            $response->setData($data);

            $event->setResponse($response);
            return;
        }

        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
            if ($exception->getPrevious()) {
                $exceptionForName = $exception->getPrevious();
            } else {
                $exceptionForName = $exception;
            }
            $response->setData([
                'errors' => [
                    [
                        'code' => $response->getStatusCode(),
                        'title' => (new \ReflectionClass($exceptionForName))->getShortName(),
                        'detail' => $this->translator->trans($exception->getMessage()),
                        'error_code' => $exception->getMessage(),
                    ]
                ]
            ]);
            $event->setResponse($response);
            return;
        }

        if ($exception instanceof AccessDeniedException) {
            $response->setStatusCode($exception->getCode());
            $data = [
                'code' => $response->getStatusCode(),
                'title' => 'AccessDenied',
                'detail' => $exception->getMessage(),
            ];
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            $data = [
                'code' => $response->getStatusCode(),
                'title' => 'InternalServerError',
                'detail' => 'Something went wrong',
            ];
        }

        if ($this->debug) {
            $classname = (new \ReflectionClass($exception))->getShortName();
            $data['debug'] = [
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

        $response->setData($data);

        // Send the modified response object to the event
        $event->setResponse($response);
    }

    /**
     * Logs an exception.
     *
     * @param \Throwable $exception The \Throwable instance
     * @param string $message The error message to log
     */
    protected function logException(\Throwable $exception, string $message, array $request)
    {
        if (null !== $this->logger) {
            if (!$exception instanceof HttpExceptionInterface || $exception->getStatusCode() >= 500) {
                $this->logger->critical($message, array('exception' => $exception, 'request' => $request));
            } else {
                $this->logger->error($message, array('exception' => $exception, 'request' => $request));
            }
        }
    }
}
