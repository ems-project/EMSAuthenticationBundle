<?php

namespace EMS\AuthenticationBundle\Controller;

use EMS\AuthenticationBundle\Firewall\KeycloakListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

class KeycloakController
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /**@var Environment */
    private $twig;

    public function __construct(TokenStorageInterface $tokenStorage, Environment $twig)
    {
        $this->tokenStorage = $tokenStorage;
        $this->twig = $twig;
    }

    public function login(Request $request): Response
    {
        if ($this->tokenStorage->getToken() instanceof AnonymousToken) {
            return new Response($this->twig->render('@EMSAuthentication/keycloak/display-error-and-logout.html.twig', [
                'logoutUrl' => $request->get(KeycloakListener::LOGOUT, null)
            ]));
        }

        $redirect = $request->get(KeycloakListener::ORIGIN_SESSION, null);
        if ($redirect === null) {
            throw new \Exception(sprintf('KeycloakListener should push the %s parameter (saved in session)', KeycloakListener::ORIGIN_SESSION));
        }

        return new RedirectResponse($redirect);
    }

    public function idpLogout()
    {
        throw new \Exception('The logout path should be configured in the security \'logout:\' parameter.');
    }

    public function idpPostLogout(): Response
    {
        return new Response($this->twig->render('@EMSAuthentication/keycloak/logout-success.html.twig'));
    }
}
