<?php

namespace EMS\AuthenticationBundle\Authentication\Provider;

use EMS\AuthenticationBundle\Authentication\Token\KeycloakUserToken;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class KeycloakAuthenticationProvider implements AuthenticationProviderInterface
{
    private $userProvider;

    public function __construct(UserProviderInterface $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    public function authenticate(TokenInterface $token)
    {
        if (!$token instanceof KeycloakUserToken) {
            throw new AuthenticationException('The Keycloak authentication failed.');
        }

        $user = $this->userProvider->loadUserByUsername($token->getUsername());

        $authenticatedToken = new KeycloakUserToken($user->getRoles());
        $authenticatedToken->setUser($user);
        $authenticatedToken->setAccessToken($token->getAccessToken());

        return $authenticatedToken;
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof KeycloakUserToken;
    }
}
