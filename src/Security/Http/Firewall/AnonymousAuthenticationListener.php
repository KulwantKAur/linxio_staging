<?php
namespace App\Security\Http\Firewall;

use App\Security\User\AnonymousUser;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

class AnonymousAuthenticationListener implements ListenerInterface
{
    private $tokenStorage;
    private $secret;
    private $authenticationManager;
    private $logger;

    public function __construct(TokenStorageInterface $tokenStorage, $secret, LoggerInterface $logger = null, AuthenticationManagerInterface $authenticationManager = null)
    {
        $this->tokenStorage = $tokenStorage;
        $this->secret = $secret;
        $this->authenticationManager = $authenticationManager;
        $this->logger = $logger;
    }

    public function handle(GetResponseEvent $event)
    {
        if (null !== $this->tokenStorage->getToken()) {
            return;
        }

        try {
            $token = new AnonymousToken($this->secret, new AnonymousUser(), []);

            if (null !== $this->authenticationManager) {
                $token = $this->authenticationManager->authenticate($token);
            }

            $this->tokenStorage->setToken($token);
        } catch (AuthenticationException $failed) {
            if (null !== $this->logger) {
                $this->logger->info('Anonymous authentication failed.', ['exception' => $failed]);
            }
        }
    }
}