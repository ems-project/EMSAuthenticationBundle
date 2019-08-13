<?php

namespace EMS\AuthenticationBundle\Authentication\Token;

use League\OAuth2\Client\Token\AccessToken;
use Stevenmaguire\OAuth2\Client\Provider\KeycloakResourceOwner;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class KeycloakUserToken extends AbstractToken
{
    const ACCESS_TOKEN = 'access_token';
    const USERNAME = 'preferred_username';

    public function __construct(array $roles = [])
    {
        parent::__construct($roles);

        // If the user has roles, consider it authenticated
        $this->setAuthenticated(count($roles) > 0);
    }

    public function getCredentials()
    {
        return '';
    }

    public function setAccessToken(AccessToken $token)
    {
        $this->setAttribute(self::ACCESS_TOKEN, $token);
    }

    public function getAccessToken(): AccessToken
    {
        return $this->getAttribute(self::ACCESS_TOKEN);
    }

    public function setUserFromResourceOwner(KeycloakResourceOwner $owner): void
    {
        $this->setUser($owner->toArray()[self::USERNAME]);
    }
}
