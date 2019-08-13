<?php

namespace EMS\AuthenticationBundle\Service;

use EMS\AuthenticationBundle\Exception\InvalidTokenException;
use League\OAuth2\Client\Token\AccessToken;
use Stevenmaguire\OAuth2\Client\Provider\Keycloak;
use Stevenmaguire\OAuth2\Client\Provider\KeycloakResourceOwner;

class KeycloakService
{
    /** @var Keycloak */
    private $provider;

    /** @var string */
    private $redirectUrl;
    /** @var string */
    private $postLogoutUrl;

    public function __construct(array $config)
    {
        $this->redirectUrl = $config['redirect_url'];
        $this->postLogoutUrl = $config['post_logout_redirect_url'];

        $this->provider = new Keycloak([
            'authServerUrl' => $config['authorize_url'],
            'realm'         => $config['realm'],
            'clientId'      => $config['client_id'],
            'clientSecret'  => $config['client_secret'],
            'redirectUri'   => $this->redirectUrl,
            'scope'         => ['openid'],
        ]);
    }

    public function getAuthorizationUrl(): string
    {
        return $this->provider->getAuthorizationUrl(['redirect_uri' => $this->redirectUrl]);
    }

    public function getAccessToken(string $code): AccessToken
    {
        try {
            return $this->provider->getAccessToken('authorization_code', [
                'code' => $code
            ]);
        } catch (\Exception $e) {
            throw new InvalidTokenException($e->getMessage());
        }
    }

    public function getResourceOwner(AccessToken $token): KeycloakResourceOwner
    {
        try {
            return $this->provider->getResourceOwner($token);
        } catch (\Exception $e) {
            throw new InvalidTokenException($e->getMessage());
        }
    }

    public function getLogoutUrl(): string
    {
        return $this->provider->getLogoutUrl(['redirect_uri' => $this->postLogoutUrl]);
    }

    public function getState(): string
    {
        return $this->provider->getState();
    }
}
