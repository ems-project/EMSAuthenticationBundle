<?php

namespace EMS\AuthenticationBundle\Firewall;

use EMS\AuthenticationBundle\Authentication\Token\KeycloakUserToken;
use EMS\AuthenticationBundle\Service\KeycloakService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class KeycloakLogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    /** @var KeycloakService */
    private $keycloak;
    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(KeycloakService $keycloak, TokenStorageInterface $tokenStorage)
    {
        $this->keycloak = $keycloak;
        $this->tokenStorage = $tokenStorage;
    }

    public function onLogoutSuccess(Request $request)
    {
        $token = $this->tokenStorage->getToken();
        if (! $token instanceof KeycloakUserToken) {
            throw new \Exception('LogoutSuccessHandler can only create response for KeycloakUserToken');
        }

        return new RedirectResponse($this->keycloak->getLogoutUrl());
    }
}
