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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Austral Security Extension.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class AustralSecurityExtension extends Extension
{
  /**
   * {@inheritdoc}
   */
  public function load(array $configs, ContainerBuilder $container)
  {
    $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
    $loader->load('command.yaml');
    $loader->load('parameters.yaml');
    $loader->load('services.yaml');

    $this->loadConfigToAustralManagerBundle($container, $loader);
  }

  /**
   * @param ContainerBuilder $container
   * @param YamlFileLoader $loader
   *
   * @return void
   * @throws \Exception
   */
  protected function loadConfigToAustralManagerBundle(ContainerBuilder $container, YamlFileLoader $loader)
  {
    $bundlesConfigPath = $container->getParameter("kernel.project_dir")."/config/bundles.php";
    if(file_exists($bundlesConfigPath))
    {
      $contents = require $bundlesConfigPath;
      if(array_key_exists("Austral\AdminBundle\AustralAdminBundle", $contents))
      {
        $loader->load('austral_admin.yaml');
      }
      $contents = require $bundlesConfigPath;
      if(array_key_exists("Austral\EntityFileBundle\AustralEntityFileBundle", $contents))
      {
        $loader->load('austral_entity_file.yaml');
      }
    }
  }

}
