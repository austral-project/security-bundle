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

use Austral\EntityBundle\Entity\Interfaces\FileInterface;
use Austral\SecurityBundle\Entity\Interfaces\UserInterface;
use Austral\SecurityBundle\Entity\User as BaseUser;

use Austral\EntityBundle\Entity\EntityInterface;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Austral User Entity.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 *
 * @ORM\Table(name="austral_security_user")
 * @ORM\Entity(repositoryClass="Austral\SecurityBundle\Repository\UserRepository")
 */
class User extends BaseUser implements UserInterface, EntityInterface, FileInterface
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
