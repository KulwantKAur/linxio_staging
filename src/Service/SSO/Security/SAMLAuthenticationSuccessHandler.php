<?php

namespace App\Service\SSO\Security;

use App\Service\SSO\SSOUserService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;

class SAMLAuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    protected function determineTargetUrl(Request $request): string
    {
        if ($this->options['always_use_default_target_path']) {
            return (string) $this->options['default_target_path'];
        }

        $relayState = $request->get(SAMLAuthenticator::ATTR_RELAY_STATE);
        if (null !== $relayState) {
            $relayState = (string) $relayState;
            if ($relayState !== $this->httpUtils->generateUri($request, $this->options['login_path'])) {
                return $relayState;
            }
        }

        return parent::determineTargetUrl($request);
    }

    public function __construct(
        HttpUtils                $httpUtils,
        protected SSOUserService $SSOUserService,
        private string           $frontSSOUrl,
        LoggerInterface          $logger,
    ) {
        parent::__construct($httpUtils, [], $logger);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse|Response
    {
        $user = $token->getUser();
        $data = $this->SSOUserService->loginUser($user, $request);

        return new RedirectResponse($this->frontSSOUrl . '?' . urldecode(http_build_query($data)));
    }
}
