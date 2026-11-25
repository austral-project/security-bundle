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

use Austral\SecurityBundle\Entity\Group as BaseGroup;
use Doctrine\ORM\Mapping as ORM;

/**
 * Austral Group Entity.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
#[ORM\Table(name: 'austral_security_group')]
#[ORM\Entity(repositoryClass: \Austral\SecurityBundle\Repository\GroupRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Group extends BaseGroup
{
  /**
   * Constructor.
   *
   * @throws \Exception
   */
  public function __construct()
  {
    parent::__construct();
  }
}
