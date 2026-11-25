<?php

/*
 * This file is part of the Austral Security Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\SecurityBundle\Event;

use Austral\SecurityBundle\Entity\Interfaces\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Austral Password Reset Event: the password of the user was reset.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class PasswordResetEvent extends Event
{
  /**
   * @param UserInterface $user
   */
  public function __construct(private readonly UserInterface $user)
  {
  }

  /**
   * getUser
   *
   * @return UserInterface
   */
  public function getUser(): UserInterface
  {
    return $this->user;
  }
}
