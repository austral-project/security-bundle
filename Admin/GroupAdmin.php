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
use Austral\AdminBundle\Admin\Event\FormAdminEvent;
use Austral\AdminBundle\Admin\Event\ListAdminEvent;

use Austral\AdminBundle\Module\RolesModule;
use Austral\FormBundle\Field as Field;

use Austral\FormBundle\Mapper\GroupFields;
use Austral\ListBundle\Column as Column;
use Austral\ListBundle\DataHydrate\DataHydrateORM;

use App\Entity\Austral\SecurityBundle\Role;

use Austral\SecurityBundle\Entity\Group;
use Austral\SecurityBundle\Entity\Interfaces\RoleInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Group Admin.
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class GroupAdmin extends Admin implements AdminModuleInterface
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
   * @param DownloadAdminEvent $downloadAdminEvent
   */
  public function configurationDownload(DownloadAdminEvent $downloadAdminEvent)
  {
    $downloadAdminEvent->getListMapper()
      ->getSection("default")
        ->buildDataHydrate(function(DataHydrateORM $dataHydrate) {
          $dataHydrate->addQueryBuilderPaginatorClosure(function(QueryBuilder $queryBuilder) {
            return $queryBuilder->orderBy("root.name", "ASC");
          });
        })
        ->addColumn(new Column\Value("name"))
        ->addColumn(new Column\Value("keyname", "fields.keyname_withoutSpan.entitled"))
        ->addColumn(new Column\Value("roles"))
        ->addColumn(new Column\Date("updated", "fields.updated.entitled", "d/m/Y"))
      ->end();
  }


  /**
   * @param ListAdminEvent $listAdminEvent
   */
  public function configureListMapper(ListAdminEvent $listAdminEvent)
  {
    $listAdminEvent->getListMapper()
      ->buildDataHydrate(function(DataHydrateORM $dataHydrate) {
        $dataHydrate->addQueryBuilderPaginatorClosure(function(QueryBuilder $queryBuilder) {
          return $queryBuilder->orderBy("root.name", "ASC");
        });
      })
      ->getSection("default")
        ->addColumn(new Column\Value("name", "fields.name.entitled"))
        ->addColumn(new Column\Value("keyname", "fields.keyname.entitled"))
        ->addColumn(new Column\Date("updated", "fields.updated.entitled", "d/m/Y"))
      ->end();
  }

  /**
   * @param FormAdminEvent $formAdminEvent
   *
   * @throws \Exception
   */
  public function configureFormMapper(FormAdminEvent $formAdminEvent)
  {
    $formAdminEvent->getFormMapper()
      ->addFieldset("fieldset.generalInformation")
        ->add(Field\TextField::create("name"))
        ->add(Field\TextField::create("keyname", array('autoConstraints'=>false)))
      ->end();

    if($this->container->has('austral.admin.modules.roles'))
    {
      $fieldsetGroupManagement = $formAdminEvent->getFormMapper()
        ->addFieldset("fieldset.rolesManagement");

      $rolesModules = $this->container->get('austral.admin.modules.roles')->initialise();

      /** @var RolesModule $rolesModule */
      foreach ($rolesModules->getRolesModules() as $rolesModule)
      {
        $groupFirst = $fieldsetGroupManagement->addGroup($rolesModule->getModuleKeyName(), $rolesModule->getModuleName());
        $this->createTableFields($groupFirst, $rolesModule);
        if($rolesModule->getChildren())
        {
          $groupFirst->setDirection(GroupFields::DIRECTION_COLUMN);
          /** @var RolesModule $child */
          foreach($rolesModule->getChildren() as $child)
          {
            $groupSecond = $groupFirst->addGroup($child->getModuleKeyName(), $child->getModuleName());
            $this->createTableFields($groupSecond, $child);
          }
        }
        else
        {
          $this->createTableFields($groupFirst, $rolesModule);
        }
      }
    }
    else
    {
      $formAdminEvent->getFormMapper()
        ->addFieldset("fieldset.generalInformation")
        ->add(Field\EntityField::create("roles", Role::class, array(
            "multiple"          =>  true,
            'query_builder'     =>  function (EntityRepository $er) {
              return $er->createQueryBuilder('u')->orderBy('u.name', 'ASC');
            }
          )
        ))
        ->end();
    }
  }

  /**
   * createFields
   *
   * @param GroupFields $group
   * @param RolesModule $rolesModule
   * @return void
   */
  protected function createTableFields(GroupFields $group, RolesModule $rolesModule)
  {
    /**
     * @var string $roleKey
     * @var RoleInterface $role
     */
    foreach($rolesModule->getRoles() as $roleKey => $role)
    {
      $group->add(Field\SwitchField::create($roleKey, array(
        "entitled"  =>  $role["name"],
        "setter"  =>  function(Group $group, $value) use($role, $rolesModule)  {
          if($value)
          {
            $group->addRole($role['object']);
            if($parent = $rolesModule->getParent())
            {
              foreach ($parent->getRoles() as $roleParent)
              {
                $group->addRole($roleParent['object']);
              }
            }
          }
          else
          {
            $group->removeRole($role['object']);
          }
        },
        'getter'  =>  function(Group $group) use($role) {
          $enabled = false;
          if(in_array($role['object']->getRole(), $group->getRolesArray()))
          {
            $enabled = true;
          }
          return $enabled;
        }
      )));
    }
  }


  /**
   * @param FormAdminEvent $formAdminEvent
   */
  protected function formUpdateBefore(FormAdminEvent $formAdminEvent)
  {
    /** @var Group $object */
    $object = $formAdminEvent->getFormMapper()->getObject();
    if(!$object->getKeyname())
    {
      $object->setKeyname($object->getName());
    }
  }





}