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

use Austral\FormBundle\Field as Field;

use Austral\ListBundle\Column as Column;
use Austral\ListBundle\DataHydrate\DataHydrateORM;

use App\Entity\Austral\SecurityBundle\Role;

use Austral\SecurityBundle\Entity\Group;
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
        ->add(Field\EntityField::create("roles", Role::class, array(
            "multiple"          =>  true,
            'query_builder'     =>  function (EntityRepository $er) {
              return $er->createQueryBuilder('u')->orderBy('u.name', 'ASC');
            }
          )
        ))
      ->end();
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