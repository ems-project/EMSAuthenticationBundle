<?php

namespace EMS\AuthenticationBundle\DependencyInjection\Factory;

use EMS\AuthenticationBundle\Authentication\Provider\KeycloakAuthenticationProvider;
use EMS\AuthenticationBundle\Firewall\KeycloakListener;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class KeycloakFactory implements SecurityFactoryInterface
{
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = sprintf('security.authentication.provider.%s.%s', $this->getKey(), $id);
        $container
            ->setDefinition($providerId, new ChildDefinition(KeycloakAuthenticationProvider::class))
            ->setArgument(0, new Reference($userProvider))
        ;

        $listenerId = sprintf('security.authentication.listener.%s.%s', $this->getKey(), $id);
        $container->setDefinition($listenerId, new ChildDefinition(KeycloakListener::class));

        return [$providerId, $listenerId, $defaultEntryPoint];
    }

    public function getPosition()
    {
        return 'pre_auth';
    }

    public function getKey()
    {
        return 'keycloak';
    }

    public function addConfiguration(NodeDefinition $node)
    {
    }
}
