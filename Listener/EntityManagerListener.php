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

use Austral\EntityBundle\Event\EntityManagerEvent;

use Austral\SecurityBundle\Entity\Interfaces\UserInterface;
use Austral\SecurityBundle\EntityManager\Interfaces\UserEntityManagerInterface;

/**
 * Austral EntityManager Listener.
 * @template T of object
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class EntityManagerListener
{

  public function __construct()
  {
  }

  /**
   * @param EntityManagerEvent<T> $entityManagerEvent
   */
  public function update(EntityManagerEvent $entityManagerEvent): void
  {
    $object = $entityManagerEvent->getObject();

    /** @var UserEntityManagerInterface<T> $entityUserManager */
    $entityUserManager = $entityManagerEvent->getEntityManager();
    if($object instanceof UserInterface)
    {
      $entityUserManager->updatePassword($object);
    }
  }

}