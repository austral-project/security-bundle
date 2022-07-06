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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Austral EntityManager Listener.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class EntityManagerListener implements EventSubscriberInterface
{

  /**
   * @var EventDispatcherInterface
   */
  protected EventDispatcherInterface $dispatcher;

  public function __construct(EventDispatcherInterface $dispatcher)
  {
    $this->dispatcher = $dispatcher;
  }

  public static function getSubscribedEvents(): array
  {
    return [
      EntityManagerEvent::EVENT_UPDATE => 'update',
    ];
  }

  /**
   * @param EntityManagerEvent $entityManagerEvent
   */
  public function update(EntityManagerEvent $entityManagerEvent)
  {
    if($entityManagerEvent->getObject() instanceof UserInterface)
    {
      $entityManagerEvent->getEntityManager()->updatePassword($entityManagerEvent->getObject());
    }
  }

}