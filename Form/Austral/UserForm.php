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

use Austral\FormBundle\Mapper\Fieldset;
use Austral\FormBundle\Mapper\FormMapper;
use Austral\FormBundle\Mapper\GroupFields;
use Austral\FormBundle\Field as Field;
use Austral\SecurityBundle\Entity\Interfaces\GroupInterface;
use Austral\SecurityBundle\Entity\Interfaces\UserInterface;
use Symfony\Component\Validator\Constraints as Constraints;
use Doctrine\ORM\EntityRepository;

/**
 * Austral UserForm.
 * @template T of object
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class UserForm
{

  /**
   * @var FormMapper<T> $formMapper
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

  /**
   * @param FormMapper<T> $formMapper
   * @param string|null $typeUserForm
   * @param string|null $formType
   */
  public function __construct(FormMapper $formMapper, ?string $typeUserForm = null, ?string $formType = "user")
  {
    $this->formMapper = $formMapper;
    $this->typeUserForm = $typeUserForm;
    $this->formType = $formType;
  }

  /**
   * form
   *
   * @param array<string, string> $language
   * @return void
   * @throws \Random\RandomException
   * @throws \Exception
   */
  public function form(array $language = array()): void
  {
    /** @var UserInterface<T>|null $object */
    $object = $this->formMapper->getObject();

    if($this->formType == "admin")
    {
      if($this->typeUserForm === UserInterface::USER_TYPE_ROOT)
      {
        $typeUser = array(
          "choices.user.type.root"    =>  UserInterface::USER_TYPE_ROOT,
          "choices.user.type.admin"   =>  UserInterface::USER_TYPE_ADMIN
        );
      }
      else {
        $typeUser = array(
          "choices.user.type.admin"   =>  UserInterface::USER_TYPE_ADMIN,
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
      ->end();

      $this->formMapper->addFieldset("fieldset.generalInformation")
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
      ->end();

      $this->formMapper->addFieldset("fieldset.connexionInformation")
        ->add(Field\TextField::create("username"))
        ->add(Field\TextField::create("email"))
        ->add(Field\PasswordField::create("plainPassword")
          ->setConstraints(!$object?->getPassword() ? array(new Constraints\NotNull()) : array())
        )
      ->end();

    if($this->formType == "admin")
    {
      $this->formMapper->addFieldset("fieldset.permissions")
        ->add(Field\EntityField::create("groups", GroupInterface::class, array(
              "multiple"          =>  true,
              'query_builder'     => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')->orderBy('u.name', 'ASC');
              },
            )
          )
        )
        ->end();
        if($this->typeUserForm === UserInterface::USER_TYPE_ROOT)
        {
          $this->formMapper->addFieldset("fieldset.permissions")
            ->add(Field\EntityField::create("securityRoles", GroupInterface::class, array(
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