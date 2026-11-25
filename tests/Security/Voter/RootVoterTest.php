<?php

/*
 * This file is part of the Austral Security Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Austral\SecurityBundle\Tests\Security\Voter;

use Austral\SecurityBundle\Entity\Interfaces\UserInterface;
use Austral\SecurityBundle\Security\Voter\RootVoter;
use Austral\SecurityBundle\Tests\Fixtures\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\Voter\RoleVoter;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

final class RootVoterTest extends TestCase
{
  public function testRootHasAllTheRights(): void
  {
    $root = new User()->setTypeUser(UserInterface::USER_TYPE_ROOT);
    $token = new UsernamePasswordToken($root, 'austral_admin', $root->getRoles());
    $vote = new Vote();

    self::assertSame(VoterInterface::ACCESS_GRANTED, new RootVoter()->vote($token, null, ['ROLE_ANYTHING'], $vote));
    self::assertSame(['The user is root.'], $vote->reasons);

    $decisionManager = new AccessDecisionManager([new RootVoter(), new RoleVoter()]);
    self::assertTrue($decisionManager->decide($token, ['ROLE_ANYTHING']));
    self::assertTrue($decisionManager->decide($token, ['edit'], new \stdClass()));
  }

  public function testTheOtherUsersAreLeftToTheOtherVoters(): void
  {
    $admin = new User()->setTypeUser(UserInterface::USER_TYPE_ADMIN);
    $token = new UsernamePasswordToken($admin, 'austral_admin', $admin->getRoles());

    self::assertSame(VoterInterface::ACCESS_ABSTAIN, new RootVoter()->vote($token, null, ['ROLE_ANYTHING']));
    self::assertSame(VoterInterface::ACCESS_ABSTAIN, new RootVoter()->vote(new NullToken(), null, ['ROLE_ANYTHING']));

    $decisionManager = new AccessDecisionManager([new RootVoter(), new RoleVoter()]);
    self::assertFalse($decisionManager->decide($token, ['ROLE_ANYTHING']));
    self::assertTrue($decisionManager->decide($token, [UserInterface::ROLE_ADMIN_ACCESS]));
  }
}
