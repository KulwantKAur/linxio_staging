<?php

namespace App\Listener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class RequestListener
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onRequest(RequestEvent $e)
    {
        $request = $e->getRequest();
        $uri = $request->getRequestUri();

        if (preg_match('/^\/api\/tracker/', $uri)) {
            $headers = json_encode($request->headers->all());

            $this->logger->notice(sprintf(
                "%s\t%s\t%s\t%s\t%s\t%s",
                $request->getClientIp(),
                $request->getMethod(),
                $request->getUri(),
                $request->getContent(),
                $request->getQueryString(),
                $headers
            ));
        }
    }
}
