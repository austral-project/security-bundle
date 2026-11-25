<?php

/*
 * This file is part of the Austral Security Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\SecurityBundle\LoginLink;

use Austral\SecurityBundle\EntityManager\Interfaces\UserEntityManagerInterface;
use Austral\SecurityBundle\Event\LoginLinkRequestedEvent;
use Austral\SecurityBundle\Event\SecurityEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Austral Login Link Requester.
 *
 * Creates a login link (login_link of Symfony, signed, with a lifetime and a maximum number of uses set in
 * the firewall) for the active user of a login and dispatches SecurityEvents::LOGIN_LINK_REQUESTED.
 * Nothing happens for an unknown or disabled user. The pages that call request() should limit the requests
 * (rate limiter).
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class LoginLinkRequester
{
  /**
   * @param UserEntityManagerInterface     $users
   * @param EventDispatcherInterface       $dispatcher
   * @param LoginLinkHandlerInterface|null $loginLinkHandler Handler of the firewall, null when login_link is not enabled
   * @param string|null                    $firewall         Firewall of the application (austral_security.login_link.firewall)
   */
  public function __construct(
    private readonly UserEntityManagerInterface $users,
    private readonly EventDispatcherInterface $dispatcher,
    private readonly ?LoginLinkHandlerInterface $loginLinkHandler = null,
    private readonly ?string $firewall = null,
  ) {
  }

  /**
   * @return bool False when the firewall has no login_link
   */
  public function isEnabled(): bool
  {
    return null !== $this->loginLinkHandler;
  }

  /**
   * @param string       $login   Email or username
   * @param Request|null $request Request of the page, for the host of the link
   *
   * @return void
   */
  public function request(string $login, ?Request $request = null): void
  {
    if (null === $this->loginLinkHandler)
    {
      throw new \LogicException(null === $this->firewall ? 'The login link is not enabled: set "austral_security.login_link.firewall" to a firewall with "login_link" in security.yaml.' : \sprintf('The login link is not enabled: add "login_link" to the firewall "%s" in security.yaml.', $this->firewall));
    }
    $user = $this->users->retrieveByLogin(trim($login));
    if (null === $user || !$user->getIsActive())
    {
      return;
    }
    $details = $this->loginLinkHandler->createLoginLink($user, $request);
    $this->dispatcher->dispatch(new LoginLinkRequestedEvent($user, $details->getUrl(), $details->getExpiresAt()), SecurityEvents::LOGIN_LINK_REQUESTED);
  }
}
