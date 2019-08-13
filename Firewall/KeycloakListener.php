<?php

namespace EMS\AuthenticationBundle\Firewall;

use EMS\AuthenticationBundle\Authentication\Token\KeycloakUserToken;
use EMS\AuthenticationBundle\Exception\InvalidStateException;
use EMS\AuthenticationBundle\Exception\SessionRequiredException;
use EMS\AuthenticationBundle\Service\KeycloakService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

class KeycloakListener implements ListenerInterface
{
    /** @var KeycloakService */
    private $keycloak;
    /** @var TokenStorageInterface  */
    protected $tokenStorage;
    /** @var AuthenticationManagerInterface  */
    protected $authenticationManager;

    const ORIGIN_SESSION = 'keycloak-origin';
    const STATE_SESSION = 'keycloak-state';
    const STATE = 'state';
    const CODE = 'code';
    const LOGOUT = 'keycloak-logout';

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        KeycloakService $keycloak
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->keycloak = $keycloak;
    }

    private function isAuthenticationRequired(string $route): bool
    {
        $token = $this->tokenStorage->getToken();
        if ($token !== null) {
            return false;
        }
        if ($route === 'keycloak_post_logout') {
            return false;
        }
        return true;
    }

    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (!$this->isAuthenticationRequired($request->attributes->get('_route'))) {
            return;
        }

        $code = $request->get(self::CODE, null);
        if ($code === null) {
            $authUrl = $this->keycloak->getAuthorizationUrl();
            $stateForLatestAuthUrl = $this->keycloak->getState();
            $this->storeParametersInSession($request, $stateForLatestAuthUrl);
            $event->setResponse(new RedirectResponse($authUrl));
            return;
        }

        $this->fetchParametersFromSession($request);
        $state = $request->get(self::STATE, null);
        $stateFromSession = $request->get(self::STATE_SESSION, null);
        if ($state === null || ($state !== $stateFromSession)) {
            throw new InvalidStateException('Invalid state, make sure HTTP sessions are enabled.');
        }

        $accessToken = $this->keycloak->getAccessToken($code);
        $user = $this->keycloak->getResourceOwner($accessToken);

        $token = new KeycloakUserToken();
        $token->setAccessToken($accessToken);
        $token->setUserFromResourceOwner($user);

        try {
            $authToken = $this->authenticationManager->authenticate($token);
            $this->tokenStorage->setToken($authToken);
        } catch (AuthenticationException $failed) {
            $request->attributes->set(self::LOGOUT, $this->keycloak->getLogoutUrl());
        }
    }

    private function storeParametersInSession(Request $request, string $state)
    {
        $session = $this->getSession($request);

        $session->set(self::ORIGIN_SESSION, $request->getRequestUri());
        $session->set(self::STATE_SESSION, $state);
    }

    private function fetchParametersFromSession(Request $request)
    {
        $session = $this->getSession($request);

        $attributes = [self::ORIGIN_SESSION, self::STATE_SESSION];

        foreach ($attributes as $attribute) {
            $request->attributes->set($attribute, $session->get($attribute, null));
            $session->remove($attribute);
        }
    }

    private function getSession(Request $request): SessionInterface
    {
        $session = $request->getSession();

        if ($session === null) {
            throw new SessionRequiredException('Keycloak authentication requires an active session');
        }

        return $session;
    }
}
