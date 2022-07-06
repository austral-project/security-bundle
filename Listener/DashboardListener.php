<?php
/*
 * This file is part of the Austral Security Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\SecurityBundle\Listener;

use Austral\AdminBundle\Event\DashboardEvent;

use Austral\AdminBundle\Dashboard\Values as DashboardValues;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Austral DashboardListener Listener.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class DashboardListener
{

  /**
   * @var ContainerInterface
   */
  protected ContainerInterface $container;
  /**
   * @var Request|null
   */
  protected ?Request $request;

  /**
   * @param ContainerInterface $container
   * @param RequestStack $requestStack
   */
  public function __construct(ContainerInterface $container, RequestStack $requestStack)
  {
    $this->container = $container;
    $this->request = $requestStack->getCurrentRequest();
  }


  /**
   * @param DashboardEvent $dashboardEvent
   *
   * @throws \Exception
   */
  public function dashboard(DashboardEvent $dashboardEvent)
  {
    $modules = $this->container->get('austral.admin.modules');

    if($modules->getModuleByKey("admin-user")->isGranted("create"))
    {
      $dashboardActionAdminUser = new DashboardValues\Action("austral_action_admin_user");
      $dashboardActionAdminUser->setEntitled("actions.create")
        ->setPosition(100)
        ->setPicto($modules->getModuleByKey("admin-user")->getPicto())
        ->setIsTranslatableText(true)
        ->setUrl($modules->getModuleByKey("admin-user")->generateUrl("create"))
        ->setTranslateParameters(array(
            "module_gender" =>  $modules->getModuleByKey("admin-user")->translateGenre(),
            "module_name"   =>  $modules->getModuleByKey("admin-user")->translateSingular()
          )
        );

      $dashboardEvent->getDashboardBlock()->getChild("austral_actions")
        ->addValue($dashboardActionAdminUser);
    }

    if($modules->getModuleByKey("user")->isGranted("create"))
    {
      $dashboardActionUser = new DashboardValues\Action("austral_action_user");
      $dashboardActionUser->setEntitled("actions.create")
        ->setPosition(100)
        ->setPicto($modules->getModuleByKey("user")->getPicto())
        ->setIsTranslatableText(true)
        ->setUrl($modules->getModuleByKey("user")->generateUrl("create"))
        ->setTranslateParameters(array(
            "module_gender" =>  $modules->getModuleByKey("user")->translateGenre(),
            "module_name"   =>  $modules->getModuleByKey("user")->translateSingular()
          )
        );

      $dashboardEvent->getDashboardBlock()->getChild("austral_actions")
        ->addValue($dashboardActionUser);
    }

  }

}