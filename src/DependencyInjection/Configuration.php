<?php

/*
 * This file is part of the Austral Security Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\SecurityBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Austral Security Configuration.
 *
 * austral_security:
 *   entities:        classes of the application (user, role, group)
 *   password_reset:  lifetime of a reset link and interval between two requests, in seconds
 *   login_link:      firewall of the application whose login_link the LoginLinkRequester uses
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class Configuration implements ConfigurationInterface
{
  /**
   * @return TreeBuilder
   */
  public function getConfigTreeBuilder(): TreeBuilder
  {
    $treeBuilder = new TreeBuilder('austral_security');
    $treeBuilder->getRootNode()
      ->addDefaultsIfNotSet()
      ->children()
        ->arrayNode('entities')
          ->addDefaultsIfNotSet()
          ->children()
            ->scalarNode('user')->cannotBeEmpty()->defaultValue('App\Entity\Austral\SecurityBundle\User')->end()
            ->scalarNode('role')->cannotBeEmpty()->defaultValue('App\Entity\Austral\SecurityBundle\Role')->end()
            ->scalarNode('group')->cannotBeEmpty()->defaultValue('App\Entity\Austral\SecurityBundle\Group')->end()
          ->end()
        ->end()
        ->arrayNode('password_reset')
          ->addDefaultsIfNotSet()
          ->children()
            ->integerNode('lifetime')->min(60)->defaultValue(3600)->info('Seconds a password reset link is valid')->end()
            ->integerNode('request_interval')->min(0)->defaultValue(60)->info('Seconds before a new link can be requested')->end()
          ->end()
        ->end()
        ->arrayNode('login_link')
          ->addDefaultsIfNotSet()
          ->children()
            ->scalarNode('firewall')->defaultNull()->info('Firewall of the application with "login_link" in security.yaml (lifetime and max_uses are set there); no login link when null')->end()
          ->end()
        ->end()
      ->end();

    return $treeBuilder;
  }
}
