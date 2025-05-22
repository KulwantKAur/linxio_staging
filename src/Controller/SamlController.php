<?php

namespace App\Controller;

use App\Service\Auth\AuthService;
use App\Service\SSO\Security\SAMLAuthenticator;
use Exception;
use RuntimeException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

#[Route('/saml')]
class SamlController extends BaseController
{
    public function __construct(
        private SAMLAuthenticator $SAMLAuthenticator,
    ) {
    }

    #[Route('/login', name: 'saml_login', methods: ['GET'])]
    public function login(Request $request)
    {
        $authErrorKey = SecurityRequestAttributes::AUTHENTICATION_ERROR;
        $session = $targetPath = $error = null;

//        if ($request->hasSession()) {
//            $session = $request->getSession();
//            $firewallName = array_slice(explode('.', trim($request->attributes->get('_firewall_context'))), -1)[0];
//            $targetPath = $session->get('_security.' . $firewallName . '.target_path');
//        }

        if ($request->attributes->has($authErrorKey)) {
            $error = $request->attributes->get($authErrorKey);
        } elseif (null !== $session && $session->has($authErrorKey)) {
            $error = $session->get($authErrorKey);
            $session->remove($authErrorKey);
        }

        if ($error instanceof Exception) {
            throw new RuntimeException($error->getMessage());
        }

        $this->SAMLAuthenticator->login($targetPath);
    }

    #[Route('/metadata', name: 'saml_metadata', methods: ['GET'])]
    public function metadata(): Response
    {
        $metadata = $this->SAMLAuthenticator->getSettings()->getSPMetadata();
        $response = new Response($metadata);
        $response->headers->set('Content-Type', 'xml');

        return $response;
    }

    /**
     * test _POST with `SAMLResponse` on https://www.base64encode.org/
     */
    #[Route('/acs', name: 'saml_acs', methods: ['POST'])]
    public function assertionConsumerService(Request $request)
    {
        throw new RuntimeException('You must configure the check path to be handled by the firewall.');
    }

    #[Route('/logout', name: 'saml_logout', methods: ['GET', 'POST'])]
    public function singleLogoutService(
        Request $request,
        SAMLAuthenticator $SAMLAuthenticator,
        AuthService $authService,
    ): RedirectResponse {
        $isLogoutResponse = $SAMLAuthenticator->isLogoutResponse($request);

        if ($isLogoutResponse) {
            $token = $SAMLAuthenticator->initLogoutAfterIdPResponse($request);
//            $tokenInBlackList = $authService->logoutByStringToken($token);

            return $SAMLAuthenticator->redirectToFrontMainUrl();
        } else {
            $token = $request->query->get('token');
            $user = $authService->getUserByToken($token);

            return $SAMLAuthenticator->logout($user, $request, $token);
        }
    }

    /**
     * for testing only
     * @todo add line to security.yml: - { path: ^/api/saml/callback, roles: PUBLIC_ACCESS }
     */
    #[Route('/callback', name: 'saml_test', methods: ['GET'])]
    public function loginLink(): Response
    {
        return $this->render('default/sso.html.twig', []);
    }
}
