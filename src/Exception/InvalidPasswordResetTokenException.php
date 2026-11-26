<?php

/*
 * This file is part of the Austral Security Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\SecurityBundle\Exception;

/**
 * The password reset token is unknown, expired, already used, or its user is disabled.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class InvalidPasswordResetTokenException extends \RuntimeException
{
  public function __construct(string $message = 'The password reset link is invalid or has expired.', ?\Throwable $previous = null)
  {
    parent::__construct($message, 0, $previous);
  }
}
