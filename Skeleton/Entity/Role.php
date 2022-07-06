##php##
/*
 * This file is autogenerate and part of the Austral Security package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Austral\SecurityBundle;

use Austral\SecurityBundle\Entity\Role as BaseRole;
use Austral\SecurityBundle\Entity\Interfaces\RoleInterface;

use Doctrine\ORM\Mapping as ORM;

/**
 * Austral Role Entity.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 *
 * @ORM\Table(name="austral_security_role")
 * @ORM\Entity(repositoryClass="Austral\SecurityBundle\Repository\RoleRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Role extends BaseRole implements RoleInterface
{
  /**
   * User constructor.
   * @throws \Exception
   */
  public function __construct()
  {
    parent::__construct();
  }
}
