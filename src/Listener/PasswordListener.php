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
use Austral\SecurityBundle\Password\PasswordUpdater;

/**
 * Austral Password Listener.
 *
 * Hashes the plain password of a user saved by an Austral entity manager (austral.entity_manager.update),
 * before it is flushed.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class PasswordListener
{
  /**
   * @param PasswordUpdater $passwordUpdater
   */
  public function __construct(private readonly PasswordUpdater $passwordUpdater)
  {
  }

  /**
   * @param EntityManagerEvent<object> $event
   *
   * @return void
   */
  public function update(EntityManagerEvent $event): void
  {
    $object = $event->getObject();
    if ($object instanceof UserInterface)
    {
      $this->passwordUpdater->update($object);
    }
  }
}
