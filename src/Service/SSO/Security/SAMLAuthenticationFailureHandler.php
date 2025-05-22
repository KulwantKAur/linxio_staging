<?php

namespace App\Service\SSO\Security;

use App\Exceptions\SSOException;
use App\Util\ExceptionHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Security\Http\HttpUtils;

class SAMLAuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    /**
     * @param KernelInterface $httpKernel
     * @param HttpUtils $httpUtils
     * @param LoggerInterface $logger
     * @param array $options
     */
    public function __construct(
        KernelInterface $httpKernel,
        HttpUtils       $httpUtils,
        LoggerInterface $logger,
        array           $options = [],
    ) {
        parent::__construct($httpKernel, $httpUtils, $options, $logger);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        // @todo implement logic with redirect with error to front-end?
        $this->logger->error(ExceptionHelper::convertToJson($exception));
        throw new SSOException($exception->getMessage());
    }
}
