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

use Doctrine\ORM\QueryBuilder;

/**
 * Role Admin.
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class RoleAdmin extends Admin implements AdminModuleInterface
{

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
        ->addColumn(new Column\Value("role"))
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
          $dataHydrate->addQueryBuilderPaginatorClosure(function(QueryBuilder $queryBuilder) {
            return $queryBuilder->orderBy("root.name", "ASC");
          });
        })
        ->addColumn(new Column\Value("name", "fields.name.entitled"))
        ->addColumn(new Column\Value("role", "fields.role.entitled"))
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
        ->add(Field\TextField::create("role"))
      ->end();
  }





}