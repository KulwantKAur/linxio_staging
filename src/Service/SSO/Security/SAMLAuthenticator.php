<?php

namespace App\Service\SSO\Security;

use App\Entity\SSOIntegrationData;
use App\Entity\User;
use App\Events\User\UserCreatedEvent;
use App\Events\User\UserUpdatedEvent;
use App\Exceptions\SSOException;
use App\Exceptions\ValidationException;
use App\Service\SSO\Provider\SSOProvider;
use App\Service\SSO\SSOService;
use App\Service\SSO\SSOUserService;
use App\Util\ExceptionHelper;
use OneLogin\Saml2\Auth as SSOAuth;
use OneLogin\Saml2\Error;
use OneLogin\Saml2\Response as SAMLResponse;
use OneLogin\Saml2\ValidationError;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\InteractiveAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

class SAMLAuthenticator implements InteractiveAuthenticatorInterface, AuthenticationEntryPointInterface
{
    private const ATTR_SAML_RESPONSE = 'SAMLResponse';
    public const ATTR_RELAY_STATE = 'RelayState';

    private array $options = [
        'useAttributeFriendlyName' => false,
        'usernameAttribute' => false,
        'mapAttributes' => [],
    ];

    /**
     * @param Request $request
     * @return SAMLResponse|null
     * @throws ValidationError
     */
    private function getResponse(Request $request)
    {
        if ($this->response) {
            return $this->response;
        }

        $SAMLResponseParam = $request->request->get(self::ATTR_SAML_RESPONSE);
        $this->response = new SAMLResponse($this->oneLoginAuth->getSettings(), $SAMLResponseParam);

        return $this->response;
    }

    /**
     * @param Request $request
     * @return SAMLResponse|null
     * @throws ValidationError
     */
    private function getLogoutResponse(Request $request)
    {
        if ($this->response) {
            return $this->response;
        }

        $SAMLResponseParam = $request->request->get(self::ATTR_SAML_RESPONSE);
        $this->response = new SAMLResponse($this->oneLoginAuth->getSettings(), $SAMLResponseParam);

        return $this->response;
    }

    /**
     * @param Request $request
     * @return string
     * @throws ValidationError
     */
    private function getIdpEntityId(Request $request): string
    {
        $issuers = $this->getResponse($request)->getIssuers();

        return reset($issuers);
    }

    /**
     * @param SSOIntegrationData $SSOIntegrationData
     * @return void
     * @throws Error
     */
    private function initNewSettingsByIdpEntityId(SSOIntegrationData $SSOIntegrationData): void
    {
        $certificate = $SSOIntegrationData->getLastEnabledCertificate();

        if (!$certificate) {
            throw new SSOException(
                'Valid certificate is not found',
                Response::HTTP_NOT_FOUND,
                new AuthenticationException('Valid certificate is not found')
            );
        }

        $newSettings = $this->SSOProvider
            ->mapSettings($SSOIntegrationData, $certificate->getCertificate(), $this->settings);
        $this->oneLoginAuth = new SSOAuth($newSettings);
    }

    /**
     * @param SSOIntegrationData $SSOIntegrationData
     * @return void
     */
    private function initNewOptionsByIdpEntityId(SSOIntegrationData $SSOIntegrationData): void
    {
        $integrationOptions = $SSOIntegrationData->getOptions();
        $options = $integrationOptions
            ? array_replace_recursive($this->getOptions(), $integrationOptions)
            : $this->getOptions();
        $this->setOptions($options);
    }

    private function handleNameId(string $username, array $attributes): array
    {
        if (!isset($attributes['email']) && filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $attributes['email'] = $username;
        }
        if (!isset($attributes['username'])) {
            $attributes['username'] = $username;
        }

        return $attributes;
    }

    private function isOnlyAuth(): bool
    {
        return boolval($this->SSOProvider?->getSSOIntegrationData()?->isOnlyAuth());
    }

    protected function createPassport(): Passport
    {
        $attributes = $this->extractAttributes();
        $username = $this->extractUsername($attributes);
        $attributes = $this->handleNameId($username, $attributes);

        $userBadge = new UserBadge(
            $username,
            function ($identifier) use ($attributes) {
                try {
                    $user = $this->userProvider->loadUserByIdentifier($identifier);

                    return $this->isOnlyAuth() ? $user : $this->updateUser($user, $attributes);
                } catch (UserNotFoundException $exception) {
                    try {
                        return $this->isOnlyAuth() ? throw $exception : $this->generateUser($identifier, $attributes);
                    } catch (ValidationException $exception) {
                        throw new SSOException(
                            $exception->getImplodedErrorsMessage(),
                            Response::HTTP_UNPROCESSABLE_ENTITY
                        );
                    }
                } catch (ValidationException $exception) {
                    throw new SSOException(
                        $exception->getImplodedErrorsMessage(),
                        Response::HTTP_UNPROCESSABLE_ENTITY
                    );
                } catch (Throwable $exception) {
                    throw new SSOException($exception->getMessage(), $exception->getCode());
                }
            }
        );

        return new SelfValidatingPassport($userBadge, [new SAMLAttributesBadge($attributes)]);
    }

    protected function extractAttributes(): array
    {
        if (isset($this->getOptions()['useAttributeFriendlyName'])
            && $this->getOptions()['useAttributeFriendlyName']
        ) {
            $attributes = $this->oneLoginAuth->getAttributesWithFriendlyName();
        } else {
            $attributes = $this->oneLoginAuth->getAttributes();
        }

        $attributes['sessionIndex'] = $this->oneLoginAuth->getSessionIndex();

        return $attributes;
    }

    protected function extractUsername(array $attributes): string
    {
        $usernameAttribute = $this->getOptions()['usernameAttribute'] ?? null;

        if ($usernameAttribute) {
            if (!array_key_exists($usernameAttribute, $attributes)) {
                $this->logger->error('Found attributes: ' . print_r($attributes, true));
                throw new SSOException(
                    'Attribute "' . $usernameAttribute . '" not found in SAML data',
                    Response::HTTP_BAD_REQUEST
                );
            }

            return $attributes[$usernameAttribute][0];
        }

        return $this->oneLoginAuth->getNameId();
    }

    protected function generateUser(string $username, array $attributes): UserInterface
    {
        $user = $this->SSOUserService->createUser($username, $attributes);
        $this->eventDispatcher->dispatch(new UserCreatedEvent($user));

        return $user;
    }

    protected function updateUser(User $user, array $attributes): UserInterface
    {
        $user = $this->SSOUserService->updateUser($user, $attributes);
        $this->eventDispatcher->dispatch(new UserUpdatedEvent($user));

        return $user;
    }

    /**
     * @param HttpUtils $httpUtils
     * @param UserProviderInterface $userProvider
     * @param SSOAuth $oneLoginAuth
     * @param AuthenticationSuccessHandlerInterface $successHandler
     * @param AuthenticationFailureHandlerInterface $failureHandler
     * @param array $settings
     * @param SSOUserService $SSOUserService
     * @param EventDispatcherInterface $eventDispatcher
     * @param LoggerInterface $logger
     * @param SSOService $SSOService
     * @param SSOProvider|null $SSOProvider
     * @param SAMLResponse|null $response
     */
    public function __construct(
        private HttpUtils                             $httpUtils,
        private UserProviderInterface                 $userProvider,
        private SSOAuth                               $oneLoginAuth,
        private AuthenticationSuccessHandlerInterface $successHandler,
        private AuthenticationFailureHandlerInterface $failureHandler,
        private array                                 $settings,
        private SSOUserService                        $SSOUserService,
        private EventDispatcherInterface              $eventDispatcher,
        private LoggerInterface                       $logger,
        private SSOService                            $SSOService,
        private string                                $frontUrl,
        private ?SSOProvider                          $SSOProvider = null,
        private ?SAMLResponse                         $response = null,
    ) {
    }

    /**
     * @param string $idpEntityId
     * @param SSOIntegrationData|null $SSOIntegrationData
     * @return void
     * @throws Error
     * @throws SSOException
     */
    public function initSSOConfigurationByIdpEntityId(
        string              $idpEntityId,
        ?SSOIntegrationData $SSOIntegrationData = null
    ): void {
        $SSOIntegrationData = $SSOIntegrationData ?: $this->SSOService->getIntegrationDataByIdpEntityId($idpEntityId);
        $this->SSOProvider = $this->SSOProvider->getInstance($SSOIntegrationData);
        $this->SSOUserService->setSSOProvider($this->SSOProvider);
        $this->initNewSettingsByIdpEntityId($SSOIntegrationData);
        $this->initNewOptionsByIdpEntityId($SSOIntegrationData);
    }

    public function supports(Request $request): ?bool
    {
        return $request->isMethod('POST') && $this->httpUtils->checkRequestPath($request, 'saml_acs');
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new RedirectResponse($this->httpUtils->generateUri($request, 'saml_login'));
    }

    public function authenticate(Request $request): Passport
    {
        $this->logger->notice(json_encode($request->request->all()));

        if ($this->getResponse($request)) {
            $this->logger->notice($this->getResponse($request)->response);
        }
        if (!$request->hasSession()) {
            throw new SSOException('This authentication method requires a session.', Response::HTTP_BAD_REQUEST);
        }

        $idpEntityId = $this->getIdpEntityId($request);
        $this->initSSOConfigurationByIdpEntityId($idpEntityId);
        $this->oneLoginAuth->processResponse();

        if ($this->oneLoginAuth->getErrors()) {
            $errorReason = $this->oneLoginAuth->getLastErrorReason();
            $this->logger->error($errorReason);
            throw new SSOException($errorReason);
        }

        return $this->createPassport();
    }

    public function createAuthenticatedToken($passport, string $firewallName): TokenInterface
    {
        if (!$passport instanceof Passport) {
            throw new SSOException(sprintf('Passport should be an instance of "%s".', Passport::class));
        }

        return $this->createToken($passport, $firewallName);
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        if (!$passport->hasBadge(SAMLAttributesBadge::class)) {
            throw new SSOException(sprintf('Passport should contains a "%s" badge.', SAMLAttributesBadge::class));
        }

        /** @var SAMLAttributesBadge $badge */
        $badge = $passport->getBadge(SAMLAttributesBadge::class);

        return new SAMLToken(
            $passport->getUser(),
            $firewallName,
            $passport->getUser()->getRoles(),
            $badge->getAttributes()
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $this->logger->notice(json_encode($request->request->all()), ['SSO' => 'Auth success']);

        return $this->successHandler->onAuthenticationSuccess($request, $token);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $this->logger->notice(json_encode($request->request->all()), ['SSO' => 'Auth fail']);

        return $this->failureHandler->onAuthenticationFailure($request, $exception);
    }

    public function isInteractive(): bool
    {
        return true;
    }

    public function login(string $targetPath)
    {
        return $this->oneLoginAuth->login($targetPath);
    }

    /**
     * @return \OneLogin\Saml2\Settings
     */
    public function getSettings()
    {
        return $this->oneLoginAuth->getSettings();
    }

    public function processSLO()
    {
        if (isset($_POST[self::ATTR_SAML_RESPONSE])) {
            $_GET[self::ATTR_SAML_RESPONSE] = $_POST[self::ATTR_SAML_RESPONSE];
        }
        if (isset($_POST[self::ATTR_RELAY_STATE])) {
            $_GET[self::ATTR_RELAY_STATE] = $_POST[self::ATTR_RELAY_STATE];
        }

        $this->oneLoginAuth->processSLO(true);

        // @todo below
        if ($errors = $this->oneLoginAuth->getErrors()) {
            $errorReason = implode(', ', $errors);
            $this->logger->error($errorReason);
            throw new SSOException($errorReason);
        }
    }

    /**
     * @return string|null
     */
    public function getSLOurl()
    {
        return $this->oneLoginAuth->getSLOurl();
    }

    /**
     * @return SSOAuth
     */
    public function getOneLoginAuth()
    {
        return $this->oneLoginAuth;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return void
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    public function logout(User $user, Request $request, string $token)
    {
        try {
            $SSOIntegrationData = $user->getSSOIntegrationData();
            $this->initSSOConfigurationByIdpEntityId($SSOIntegrationData->getIdpEntityId(), $SSOIntegrationData);
            $this->processSLO();
        } catch (Error $e) {
            if (!empty($this->getSLOurl())) {
//                $sessionIndex = $token->hasAttribute('sessionIndex') ? $token->getAttribute('sessionIndex') : null;
                $logoutUrl = $this->httpUtils->generateUri($request, 'saml_logout');

                return $this->oneLoginAuth->logout(
                    $logoutUrl . '?' . urldecode(http_build_query([
                        'email' => $user->getEmail(),
                        'token' => $token
                    ])),
                    ['email' => $user->getEmail()],
                    $user->getEmail()
                );
            }
        } catch (\Exception $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));
            throw new SSOException($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return string|null
     */
    public function getUserIdentityFromLogoutResponse(Request $request): ?string
    {
        $responseRelayState = $request->request->get(self::ATTR_RELAY_STATE);

        if ($responseRelayState) {
            parse_str(parse_url($responseRelayState, PHP_URL_QUERY), $data);
            $userIdentity = $data['email'] ?? null;
        }

        return $userIdentity ?? null;
    }

    /**
     * @param Request $request
     * @return string|null
     */
    public function getUserTokenFromLogoutResponse(Request $request): ?string
    {
        $responseRelayState = $request->request->get(self::ATTR_RELAY_STATE);

        if ($responseRelayState) {
            parse_str(parse_url($responseRelayState, PHP_URL_QUERY), $data);
            $token = $data['token'] ?? null;
        }

        return $token ?? null;
    }

    /**
     * @param Request $request
     * @return string|null
     */
    public function isLogoutResponse(Request $request)
    {
        return $request->request->get(self::ATTR_SAML_RESPONSE);
    }

    /**
     * @return RedirectResponse
     */
    public function redirectToFrontMainUrl(): RedirectResponse
    {
        return new RedirectResponse($this->frontUrl);
    }

    /**
     * @param $request
     * @return string
     * @throws Error
     * @throws SSOException
     * @throws ValidationError
     */
    public function initLogoutAfterIdPResponse($request): string
    {
        if ($this->getResponse($request)) {
            $this->logger->info($this->getResponse($request)->response);
        }

        $userIdentity = $this->getUserIdentityFromLogoutResponse($request);
        $token = $this->getUserTokenFromLogoutResponse($request);

        if (!$userIdentity || !$token) {
            throw new SSOException('User is not found', Response::HTTP_NOT_FOUND, new UserNotFoundException());
        }

        $user = $this->userProvider->loadUserByIdentifier($userIdentity);
        $this->logout($user, $request, $token);

        return $token;
    }
}
