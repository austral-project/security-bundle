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
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\OfflineTokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecision;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\UserAuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

/**
 * Replaces Symfony\Component\Security\Core\Authorization\AuthorizationChecker
 * (service "security.authorization_checker"): same behaviour, except that a "root" user is granted everything.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 *
 * @final
 */
class AuthorizationChecker implements AuthorizationCheckerInterface, UserAuthorizationCheckerInterface
{

  /**
   * @var array<TokenInterface>
   */
  private array $tokenStack = [];

  /**
   * @var array<AccessDecision>
   */
  private array $accessDecisionStack = [];

  /**
   * AuthorizationChecker constructor
   *
   * @param TokenStorageInterface $tokenStorage
   * @param AccessDecisionManagerInterface $accessDecisionManager
   */
  public function __construct(
    protected TokenStorageInterface $tokenStorage,
    protected AccessDecisionManagerInterface $accessDecisionManager
  )
  {
  }

  /**
   * {@inheritdoc}
   */
  final public function isGranted(mixed $attribute, mixed $subject = null, ?AccessDecision $accessDecision = null): bool
  {
    $token = end($this->tokenStack) ?: $this->tokenStorage->getToken();
    if (!$token || !$token->getUser()) {
      $token = new NullToken();
    }

    $accessDecision ??= end($this->accessDecisionStack) ?: new AccessDecision();

    $user = $token->getUser();
    if($user instanceof UserInterface && $user->getTypeUser() === "root")
    {
      return $accessDecision->isGranted = true;
    }

    $this->accessDecisionStack[] = $accessDecision;
    try {
      return $accessDecision->isGranted = $this->accessDecisionManager->decide($token, [$attribute], $subject, $accessDecision);
    } finally {
      array_pop($this->accessDecisionStack);
    }
  }

  /**
   * {@inheritdoc}
   */
  final public function isGrantedForUser(SymfonyUserInterface $user, mixed $attribute, mixed $subject = null, ?AccessDecision $accessDecision = null): bool
  {
    $token = new class($user->getRoles()) extends AbstractToken implements OfflineTokenInterface {};
    $token->setUser($user);
    $this->tokenStack[] = $token;

    try {
      return $this->isGranted($attribute, $subject, $accessDecision);
    } finally {
      array_pop($this->tokenStack);
    }
  }

}
