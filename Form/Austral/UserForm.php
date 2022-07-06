<?php
/*
 * This file is part of the Austral Security Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\SecurityBundle\Form\Austral;

use App\Entity\Austral\SecurityBundle\Group;
use App\Entity\Austral\SecurityBundle\Role;
use App\Entity\Austral\SecurityBundle\User;
use Austral\FormBundle\Mapper\Fieldset;
use Austral\FormBundle\Mapper\FormMapper;
use Austral\FormBundle\Mapper\GroupFields;
use Austral\FormBundle\Field as Field;
use Symfony\Component\Validator\Constraints as Constraints;
use Doctrine\ORM\EntityRepository;

/**
 * Austral UserForm.
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class UserForm
{

  /**
   * @var FormMapper $formMapper
   */
  protected FormMapper $formMapper;

  /**
   * @var string|null
   */
  protected ?string $typeUserForm = null;

  /**
   * @var string|null
   */
  protected ?string $formType = null;

  public function __construct(FormMapper $formMapper, ?string $typeUserForm = null, ?string $formType = "user")
  {
    $this->formMapper = $formMapper;
    $this->typeUserForm = $typeUserForm;
    $this->formType = $formType;
  }

  /**
   * @throws \Exception
   */
  public function form(array $language = array())
  {
    if($this->formType == "admin")
    {
      if($this->typeUserForm === User::USER_TYPE_ROOT)
      {
        $typeUser = array(
          "choices.user.type.root"    =>  User::USER_TYPE_ROOT,
          "choices.user.type.admin"   =>  User::USER_TYPE_ADMIN
        );
      }
      else {
        $typeUser = array(
          "choices.user.type.admin"   =>  User::USER_TYPE_ADMIN,
        );
      }
      $this->formMapper->addFieldset("fieldset.right")
        ->setPositionName(Fieldset::POSITION_RIGHT)
        ->setViewName(false)
        ->add(Field\SelectField::create("typeUser", $typeUser));
    }



    $this->formMapper->addFieldset("fieldset.right")
      ->setPositionName(Fieldset::POSITION_RIGHT)
      ->setViewName(false)
      ->add(Field\ChoiceField::create("isActive", array(
        "choices.status.disabled"    =>  false,
        "choices.status.enabled"     =>  true,
      )))
      ->add(Field\SelectField::create("language",
          $language,
          array("required"=>true)
        )
      )
      ->end()
      ->addFieldset("fieldset.generalInformation")
        ->addGroup("name")
          ->add(Field\TextField::create("lastname", array(
              "group"     =>  array(
                "size" =>  GroupFields::SIZE_COL_6)
              )
            )
          )
          ->add(Field\TextField::create("firstname", array(
                "group"     =>  array(
                  "size" =>  GroupFields::SIZE_COL_6)
              )
            )
          )
        ->end()
        ->add(Field\UploadField::create("avatar"))
        ->add(Field\ColorPicker::create("avatarColor", array(
          "colorpicker-options"  =>  array()
        )))
      ->end()
      ->addFieldset("fieldset.connexionInformation")
        ->add(Field\TextField::create("username"))
        ->add(Field\TextField::create("email"))
        ->add(Field\PasswordField::create("plainPassword")->setConstraints(!$this->formMapper->getObject()->getPassword() ? array(new Constraints\NotNull()) : array()))
      ->end();

    if($this->formType == "admin")
    {
      $this->formMapper->addFieldset("fieldset.permissions")
        ->add(Field\EntityField::create("groups", Group::class, array(
              "multiple"          =>  true,
              'query_builder'     => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')->orderBy('u.name', 'ASC');
              },
            )
          )
        )
        ->end();
        if($this->typeUserForm === User::USER_TYPE_ROOT)
        {
          $this->formMapper->addFieldset("fieldset.permissions")
            ->add(Field\EntityField::create("securityRoles", Role::class, array(
                "multiple"          =>  true,
                'query_builder'     => function (EntityRepository $er) {
                  return $er->createQueryBuilder('u')->orderBy('u.name', 'ASC');
                }
              )
            )
          )
          ->end();
        }
    }
  }


}