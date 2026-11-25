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

/**
 * Austral Security Events.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class SecurityEvents
{
  /**
   * PasswordResetRequestedEvent: a password reset was requested, the link must be sent to the user.
   */
  public const string PASSWORD_RESET_REQUESTED = 'austral.security.password_reset.requested';

  /**
   * PasswordResetEvent: the password was reset with a valid token.
   */
  public const string PASSWORD_RESET = 'austral.security.password_reset.done';

  /**
   * LoginLinkRequestedEvent: a login link was requested, it must be sent to the user.
   */
  public const string LOGIN_LINK_REQUESTED = 'austral.security.login_link.requested';

  private function __construct()
  {
  }
}
