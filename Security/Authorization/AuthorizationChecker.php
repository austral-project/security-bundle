<?php
/*
 * This file is part of the Austral Security Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Austral\SecurityBundle\Security\Authorization;

use Austral\SecurityBundle\Entity\Interfaces\UserInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

/**
 * AuthorizationChecker is the main authorization point of the Security component extends to Symfony\Component\Security\Core\Authorization\AuthorizationChecker .
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 *
 * @final
 */
class AuthorizationChecker implements AuthorizationCheckerInterface
{
  /**
   * @var TokenStorageInterface
   */
  private TokenStorageInterface $tokenStorage;

  /**
   * @var AccessDecisionManagerInterface
   */
  private AccessDecisionManagerInterface $accessDecisionManager;

  /**
   * @var AuthenticationManagerInterface
   */
  private AuthenticationManagerInterface $authenticationManager;

  /**
   * @var bool
   */
  private bool $exceptionOnNoToken;

  /**
   * AuthorizationChecker constructor
   *
   * @param TokenStorageInterface $tokenStorage
   * @param $accessDecisionManager
   * @param $alwaysAuthenticate
   * @param $exceptionOnNoToken
   */
  public function __construct(TokenStorageInterface $tokenStorage, /* AccessDecisionManagerInterface */ $accessDecisionManager, /* bool */ $alwaysAuthenticate = false, /* bool */ $exceptionOnNoToken = true)
  {
    if (!$accessDecisionManager instanceof AccessDecisionManagerInterface) {
      throw new \TypeError(sprintf('Argument 2 of "%s" must be instance of "%s", "%s" given.', __METHOD__, AccessDecisionManagerInterface::class, get_debug_type($accessDecisionManager)));
    }
    $this->tokenStorage = $tokenStorage;
    $this->accessDecisionManager = $accessDecisionManager;
    $this->exceptionOnNoToken = $exceptionOnNoToken;
  }

  /**
   * {@inheritdoc}
   *
   * @throws AuthenticationCredentialsNotFoundException when the token storage has no authentication token and $exceptionOnNoToken is set to true
   */
  final public function isGranted($attribute, $subject = null, $exceptionOnNoToken = false): bool
  {
    $token = $this->tokenStorage->getToken();
    if (!$token || !$token->getUser()) {
      if ($this->exceptionOnNoToken && $exceptionOnNoToken !== false) {
        throw new AuthenticationCredentialsNotFoundException('The token storage contains no authentication token. One possible reason may be that there is no firewall configured for this URL.');
      }
      $token = new NullToken();
    }

    $user = $token->getUser();
    if($user instanceof UserInterface && $user->getTypeUser() === "root")
    {
      return true;
    }
    return $this->accessDecisionManager->decide($token, [$attribute], $subject);
  }

}
