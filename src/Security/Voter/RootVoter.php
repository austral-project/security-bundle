<?php

/*
 * This file is part of the Austral Security Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\SecurityBundle\Security\Voter;

use Austral\SecurityBundle\Entity\Interfaces\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Austral Root Voter: a root user (type "root") is granted every attribute.
 *
 * With the default strategy of Symfony (affirmative), one granted vote wins over the denied ones. With the
 * strategies "consensus" or "unanimous", a voter that denies can still deny a root user.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class RootVoter implements VoterInterface
{
  /**
   * @param TokenInterface $token
   * @param mixed          $subject
   * @param array<mixed>   $attributes
   * @param Vote|null      $vote
   *
   * @return int
   */
  public function vote(TokenInterface $token, mixed $subject, array $attributes, ?Vote $vote = null): int
  {
    $user = $token->getUser();
    if ($user instanceof UserInterface && $user->isSuperAdmin())
    {
      $vote?->addReason('The user is root.');

      return self::ACCESS_GRANTED;
    }

    return self::ACCESS_ABSTAIN;
  }
}
