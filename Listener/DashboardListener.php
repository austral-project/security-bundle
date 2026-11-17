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
use Austral\AdminBundle\Module\Modules;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Austral DashboardListener Listener.
 * @template T of object
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class DashboardListener
{

  /**
   * @var Request|null
   */
  protected ?Request $request = null;

  /**
   * DashboardListener constructor
   *
   * @param Modules<T> $modules
   */
  public function __construct(
    #[Autowire(service: "austral.admin.modules")] protected Modules $modules,
  )
  {
  }

  /**
   * setRequestStack
   *
   * @param RequestStack $requestStack
   * @return $this
   */
  public function setRequestStack(RequestStack $requestStack): self
  {
    $this->request = $requestStack->getCurrentRequest();
    return $this;
  }


  /**
   * @param DashboardEvent<T> $dashboardEvent
   *
   * @throws \Exception
   */
  public function dashboard(DashboardEvent $dashboardEvent): void
  {

    $moduleAdminUser = $this->modules->getModuleByKey("admin-user");
    if($moduleAdminUser && $moduleAdminUser->isGranted("create"))
    {
      $dashboardActionAdminUser = new DashboardValues\Action("austral_action_admin_user");
      $dashboardActionAdminUser->setEntitled("actions.create")
        ->setPosition(100)
        ->setPicto($moduleAdminUser->getPicto())
        ->setIsTranslatableText(true)
        ->setUrl($moduleAdminUser->generateUrl("create"))
        ->setTranslateParameters(array(
            "module_gender" =>  $moduleAdminUser->translateGenre(),
            "module_name"   =>  $moduleAdminUser->translateSingular()
          )
        );
      $dashboardEvent->getDashboardBlock()->getChild("austral_actions")?->addValue($dashboardActionAdminUser);
    }

    $moduleUser = $this->modules->getModuleByKey("admin-user");
    if($moduleUser && $moduleUser->isGranted("create"))
    {
      $dashboardActionUser = new DashboardValues\Action("austral_action_user");
      $dashboardActionUser->setEntitled("actions.create")
        ->setPosition(100)
        ->setPicto($moduleUser->getPicto())
        ->setIsTranslatableText(true)
        ->setUrl($moduleUser->generateUrl("create"))
        ->setTranslateParameters(array(
            "module_gender" =>  $moduleUser->translateGenre(),
            "module_name"   =>  $moduleUser->translateSingular()
          )
        );
      $dashboardEvent->getDashboardBlock()->getChild("austral_actions")?->addValue($dashboardActionUser);
    }

  }

}