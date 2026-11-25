<?php

/*
 * This file is part of the Austral Security Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Austral\SecurityBundle;

use Austral\SecurityBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * Austral User Entity.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
#[ORM\Table(name: 'austral_security_user')]
#[ORM\Entity(repositoryClass: \Austral\SecurityBundle\Repository\UserRepository::class)]
class User extends BaseUser
{
  /**
   * User constructor.
   *
   * @throws \Exception
   */
  public function __construct()
  {
    parent::__construct();
  }
}
