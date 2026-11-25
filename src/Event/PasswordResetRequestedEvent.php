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
 * Austral Password Reset Requested Event.
 *
 * The listener (email-bundle) sends the URL to the user. The token is given once: only its hash is stored.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class PasswordResetRequestedEvent extends Event
{
  /**
   * @param UserInterface      $user
   * @param string             $token     Token of the reset, in the URL
   * @param string             $url       URL of the page that resets the password
   * @param \DateTimeImmutable $expiresAt
   */
  public function __construct(
    private readonly UserInterface $user,
    #[\SensitiveParameter] private readonly string $token,
    #[\SensitiveParameter] private readonly string $url,
    private readonly \DateTimeImmutable $expiresAt,
  ) {
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

  /**
   * getToken
   *
   * @return string
   */
  public function getToken(): string
  {
    return $this->token;
  }

  /**
   * getUrl
   *
   * @return string
   */
  public function getUrl(): string
  {
    return $this->url;
  }

  /**
   * getExpiresAt
   *
   * @return \DateTimeImmutable
   */
  public function getExpiresAt(): \DateTimeImmutable
  {
    return $this->expiresAt;
  }
}
