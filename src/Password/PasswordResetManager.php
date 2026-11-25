<?php

/*
 * This file is part of the Austral Security Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\SecurityBundle\Password;

use Austral\SecurityBundle\Entity\Interfaces\UserInterface;
use Austral\SecurityBundle\EntityManager\Interfaces\UserEntityManagerInterface;
use Austral\SecurityBundle\Event\PasswordResetEvent;
use Austral\SecurityBundle\Event\PasswordResetRequestedEvent;
use Austral\SecurityBundle\Event\SecurityEvents;
use Austral\SecurityBundle\Exception\InvalidPasswordResetTokenException;
use Psr\Clock\ClockInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Austral Password Reset Manager.
 *
 * A token is a selector (to find the user) followed by a verifier. Only the selector and the SHA-256 of
 * the verifier are stored, with the expiry date: a copy of the database gives no usable link. The token
 * is compared in constant time and can be used once.
 *
 * request() gives no clue whether the user exists. A new request within the request interval sends
 * nothing: the pages that call request() should also limit the requests (rate limiter).
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class PasswordResetManager
{
  private const int SELECTOR_BYTES = 12;
  private const int VERIFIER_BYTES = 24;

  /**
   * @param UserEntityManagerInterface $users
   * @param EventDispatcherInterface   $dispatcher
   * @param int                        $lifetime        Seconds a reset link is valid
   * @param int                        $requestInterval Seconds before a new link can be requested
   * @param ClockInterface|null        $clock
   */
  public function __construct(
    private readonly UserEntityManagerInterface $users,
    private readonly EventDispatcherInterface $dispatcher,
    private readonly int $lifetime = 3600,
    private readonly int $requestInterval = 60,
    private readonly ?ClockInterface $clock = null,
  ) {
    if ($lifetime < 1 || $requestInterval < 0)
    {
      throw new \InvalidArgumentException('The lifetime of a password reset must be at least 1 second and its request interval positive.');
    }
  }

  /**
   * Starts a password reset for the active user of this login (email or username) and dispatches
   * SecurityEvents::PASSWORD_RESET_REQUESTED with the link. Nothing happens for an unknown or disabled user.
   *
   * @param string                   $login
   * @param \Closure(string): string $url   URL of the reset page for a token
   *
   * @return void
   */
  public function request(string $login, \Closure $url): void
  {
    $user = $this->users->retrieveByLogin(trim($login));
    if (null === $user || !$user->getIsActive())
    {
      return;
    }
    $now = $this->now();
    if (null !== ($expiresAt = $user->getPasswordResetExpiresAt()) && $expiresAt > $now)
    {
      $requestedAt = $expiresAt->modify("-{$this->lifetime} seconds");
      if ($requestedAt > $now->modify("-{$this->requestInterval} seconds"))
      {
        return;
      }
    }

    $selector = bin2hex(random_bytes(self::SELECTOR_BYTES));
    $verifier = bin2hex(random_bytes(self::VERIFIER_BYTES));
    $expiresAt = $now->modify("+{$this->lifetime} seconds");
    $user->setPasswordReset($selector, self::hash($verifier), $expiresAt);
    $this->users->updateUser($user);

    $token = $selector.$verifier;
    $this->dispatcher->dispatch(new PasswordResetRequestedEvent($user, $token, $url($token), $expiresAt), SecurityEvents::PASSWORD_RESET_REQUESTED);
  }

  /**
   * The active user of a valid token, null otherwise.
   *
   * @param string $token
   *
   * @return UserInterface|null
   */
  public function verify(#[\SensitiveParameter] string $token): ?UserInterface
  {
    $selectorLength = self::SELECTOR_BYTES * 2;
    if (!preg_match('/^[0-9a-f]{'.($selectorLength + self::VERIFIER_BYTES * 2).'}$/', $token))
    {
      return null;
    }
    $user = $this->users->retrieveByPasswordResetSelector(substr($token, 0, $selectorLength));
    if (null === $user || !$user->getIsActive())
    {
      return null;
    }
    $hash = $user->getPasswordResetHash();
    $expiresAt = $user->getPasswordResetExpiresAt();
    if (null === $hash || null === $expiresAt || $expiresAt <= $this->now())
    {
      return null;
    }

    return hash_equals($hash, self::hash(substr($token, $selectorLength))) ? $user : null;
  }

  /**
   * Sets the new password of the user of a valid token, clears the token and dispatches
   * SecurityEvents::PASSWORD_RESET.
   *
   * @param string $token
   * @param string $plainPassword
   *
   * @return UserInterface
   *
   * @throws InvalidPasswordResetTokenException
   */
  public function reset(#[\SensitiveParameter] string $token, #[\SensitiveParameter] string $plainPassword): UserInterface
  {
    if ('' === $plainPassword)
    {
      throw new \InvalidArgumentException('The new password cannot be empty.');
    }
    $user = $this->verify($token) ?? throw new InvalidPasswordResetTokenException();
    $user->setPlainPassword($plainPassword);
    $user->clearPasswordReset();
    $this->users->updateUser($user);
    $this->dispatcher->dispatch(new PasswordResetEvent($user), SecurityEvents::PASSWORD_RESET);

    return $user;
  }

  /**
   * @return int Seconds a reset link is valid
   */
  public function getLifetime(): int
  {
    return $this->lifetime;
  }

  /**
   * @param string $verifier
   *
   * @return string
   */
  private static function hash(string $verifier): string
  {
    return hash('sha256', $verifier);
  }

  /**
   * @return \DateTimeImmutable
   */
  private function now(): \DateTimeImmutable
  {
    return $this->clock?->now() ?? new \DateTimeImmutable();
  }
}
