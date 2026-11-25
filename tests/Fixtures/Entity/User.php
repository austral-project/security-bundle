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

namespace Austral\SecurityBundle\Tests\Fixtures\Entity;

use Austral\SecurityBundle\Entity\User as BaseUser;
use Austral\SecurityBundle\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * User of the application, as in the Skeleton.
 */
#[ORM\Table(name: 'austral_security_user')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User extends BaseUser
{
}
