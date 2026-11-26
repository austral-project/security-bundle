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
 * Austral Login Link Requested Event.
 *
 * The listener (email-bundle) sends the URL to the user.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class LoginLinkRequestedEvent extends Event
{
  /**
   * @param UserInterface      $user
   * @param string             $url       Signed URL that logs the user in
   * @param \DateTimeImmutable $expiresAt
   */
  public function __construct(
    private readonly UserInterface $user,
    #[\SensitiveParameter] private readonly string $url,
    private readonly \DateTimeImmutable $expiresAt,
  ) {
  }

  public function getUser(): UserInterface
  {
    return $this->user;
  }

  public function getUrl(): string
  {
    return $this->url;
  }

  public function getExpiresAt(): \DateTimeImmutable
  {
    return $this->expiresAt;
  }
}
