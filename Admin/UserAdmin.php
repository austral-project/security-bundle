<?php
/*
 * This file is part of the Austral Security Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace Austral\SecurityBundle\Admin;

use Austral\AdminBundle\Admin\Admin;
use Austral\AdminBundle\Admin\AdminModuleInterface;
use Austral\AdminBundle\Admin\Event\DownloadAdminEvent;
use Austral\AdminBundle\Admin\Event\FilterEventInterface;
use Austral\AdminBundle\Admin\Event\FormAdminEvent;
use Austral\AdminBundle\Admin\Event\ListAdminEvent;
use Austral\FilterBundle\Filter\Type as FilterType;

use Austral\ListBundle\Column as Column;
use Austral\ListBundle\DataHydrate\DataHydrateORM;

use App\Entity\Austral\SecurityBundle\User;

use Austral\SecurityBundle\Entity\Interfaces\UserInterface;
use Austral\SecurityBundle\Form\Austral\UserForm;

use Doctrine\ORM\QueryBuilder;

/**
 * User .
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class UserAdmin extends Admin implements AdminModuleInterface
{

  /**
   * @return array
   */
  public function getEvents() : array
  {
    return array(
      FormAdminEvent::EVENT_UPDATE_BEFORE =>  "formUpdateBefore"
    );
  }

  /**
   * @param FilterEventInterface $listAdminEvent
   *
   * @throws \Exception
   */
  public function configureFilterMapper(FilterEventInterface $listAdminEvent)
  {
    $listAdminEvent->getFilterMapper()->filter("default")
      ->add(new FilterType\StringType("email"))
      ->add(new FilterType\StringType("firstname"))
      ->add(new FilterType\StringType("lastname"));
  }

  /**
   * @param DownloadAdminEvent $downloadAdminEvent
   */
  public function configurationDownload(DownloadAdminEvent $downloadAdminEvent)
  {
    $downloadAdminEvent->getListMapper()
      ->getSection("default")
        ->buildDataHydrate(function(DataHydrateORM $dataHydrate) {
          $dataHydrate->addQueryBuilderClosure(function(QueryBuilder $queryBuilder) {
            return $queryBuilder->where("root.typeUser = :userType")
              ->setParameter("userType", "user");
          });
          $dataHydrate->addQueryBuilderPaginatorClosure(function(QueryBuilder $queryBuilder) {
            return $queryBuilder->orderBy("root.username", "ASC");
          });
        })
        ->addColumn(new Column\Value("username"))
        ->addColumn(new Column\Value("email"))
        ->addColumn(new Column\Value("typeUser"))
        ->addColumn(new Column\Value("lastname"))
        ->addColumn(new Column\Value("firstname"))
        ->addColumn(new Column\Date("updated", "fields.updated.entitled", "d/m/Y"))
      ->end();
  }

  /**
   * @param ListAdminEvent $listAdminEvent
   */
  public function configureListMapper(ListAdminEvent $listAdminEvent)
  {
    $listAdminEvent->getListMapper()
      ->getSection("default")
        ->buildDataHydrate(function(DataHydrateORM $dataHydrate) {
          $dataHydrate->addQueryBuilderClosure(function(QueryBuilder $queryBuilder) {
            return $queryBuilder->where("root.typeUser = :userType")
              ->setParameter("userType", "user");
          });
          $dataHydrate->addQueryBuilderPaginatorClosure(function(QueryBuilder $queryBuilder) {
            return $queryBuilder->orderBy("root.username", "ASC");
          });
        })
        ->setMaxResult(100)
        ->addColumn(new Column\Image("avatar", null, array(), "@AustralSecurity/List/Components/avatar.html.twig"))
        ->addColumn(new Column\Value("username"))
        ->addColumn(new Column\Value("email"))
        ->addColumn(new Column\Value("typeUser"))
        ->addColumn(new Column\SwitchValue("isActive", "fields.enabled.entitled", 0, 1,
            $listAdminEvent->getCurrentModule()->generateUrl("change"),
            $listAdminEvent->getCurrentModule()->isGranted("edit")
          )
        )
        ->addColumn(new Column\Date("updated", "fields.updated.entitled", "d/m/Y"))
      ->end();
  }

  /**
   * @param FormAdminEvent $formAdminEvent
   *
   * @throws \ReflectionException
   * @throws \Exception
   */
  public function configureFormMapper(FormAdminEvent $formAdminEvent)
  {

    $language = array();
    foreach($this->container->get("austral.admin.config")->get("language.user") as $value)
    {
      $language["choices.language.{$value}"] = $value;
    }
    $userForm = new UserForm($formAdminEvent->getFormMapper(), $formAdminEvent->getAdminHandler()->getUser()->getTypeUser(), "user");
    $userForm->form($language);
  }

  /**
   * @param FormAdminEvent $formAdminEvent
   */
  protected function formUpdateBefore(FormAdminEvent $formAdminEvent)
  {
    /** @var UserInterface $object */
    $object = $formAdminEvent->getFormMapper()->getObject();
    if($object->getId() === $formAdminEvent->getAdminHandler()->getUser()->getId())
    {
      $this->container->get("session")->set("austral_language_interface", $object->getLanguage());
      $this->container->get("translator")->setLocale($object->getLanguage());
    }
    if(!$object->getTypeUser())
    {
      $object->setTypeUser(User::USER_TYPE_USER);
    }
  }



}