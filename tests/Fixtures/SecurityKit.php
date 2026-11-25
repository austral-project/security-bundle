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

namespace Austral\SecurityBundle\Tests\Fixtures;

use Austral\EntityBundle\EventSubscriber\EntityManagerEventSubscriber;
use Austral\SecurityBundle\Entity\Interfaces\GroupInterface;
use Austral\SecurityBundle\Entity\Interfaces\RoleInterface;
use Austral\SecurityBundle\Entity\Interfaces\UserInterface;
use Austral\SecurityBundle\EntityManager\GroupEntityManager;
use Austral\SecurityBundle\EntityManager\RoleEntityManager;
use Austral\SecurityBundle\EntityManager\UserEntityManager;
use Austral\SecurityBundle\Listener\PasswordListener;
use Austral\SecurityBundle\Password\PasswordUpdater;
use Austral\SecurityBundle\Tests\Fixtures\Entity\Group;
use Austral\SecurityBundle\Tests\Fixtures\Entity\Role;
use Austral\SecurityBundle\Tests\Fixtures\Entity\User;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Tools\ResolveTargetEntityListener;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;

/**
 * The services of the bundle on an in-memory SQLite database, as the container wires them.
 */
final class SecurityKit
{
  public readonly EntityManagerInterface $doctrine;
  public readonly EventDispatcher $dispatcher;
  public readonly UserPasswordHasher $hasher;
  public readonly PasswordUpdater $passwordUpdater;
  public readonly UserEntityManager $users;
  public readonly RoleEntityManager $roles;
  public readonly GroupEntityManager $groups;

  public function __construct()
  {
    $config = new Configuration();
    $config->setMetadataDriverImpl(new AttributeDriver([__DIR__.'/Entity']));
    $config->enableNativeLazyObjects(true);

    $resolver = new ResolveTargetEntityListener();
    $resolver->addResolveTargetEntity(UserInterface::class, User::class, []);
    $resolver->addResolveTargetEntity(RoleInterface::class, Role::class, []);
    $resolver->addResolveTargetEntity(GroupInterface::class, Group::class, []);
    $eventManager = new EventManager();
    $eventManager->addEventListener([Events::loadClassMetadata, Events::onClassMetadataNotFound], $resolver);

    $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true], $config);
    $this->doctrine = new EntityManager($connection, $config, $eventManager);
    new SchemaTool($this->doctrine)->createSchema($this->doctrine->getMetadataFactory()->getAllMetadata());

    // Cheap hasher for the tests; the applications use "auto".
    $this->hasher = new UserPasswordHasher(new PasswordHasherFactory([
      UserInterface::class => ['algorithm' => 'bcrypt', 'cost' => 4],
    ]));
    $this->passwordUpdater = new PasswordUpdater($this->hasher);

    $this->dispatcher = new EventDispatcher();
    $this->dispatcher->addSubscriber(new EntityManagerEventSubscriber());
    $this->dispatcher->addListener('austral.entity_manager.update', new PasswordListener($this->passwordUpdater)->update(...));

    $this->users = new UserEntityManager($this->doctrine, $this->dispatcher, User::class);
    $this->roles = new RoleEntityManager($this->doctrine, $this->dispatcher, Role::class);
    $this->groups = new GroupEntityManager($this->doctrine, $this->dispatcher, Group::class);
  }

  /**
   * An active user saved with this password.
   */
  public function user(string $email = 'jane@austral.dev', string $password = 'secret', ?string $username = null): User
  {
    $user = new User();
    $user->setEmail($email)->setUsername($username ?? $email)->setIsActive(true)->setPlainPassword($password);
    $this->users->updateUser($user);

    return $user;
  }

  public function role(string $role, ?string $name = null): Role
  {
    $entity = new Role();
    $entity->setRole($role)->setName($name ?? $role);
    $this->roles->update($entity);

    return $entity;
  }

  public function group(string $keyname): Group
  {
    $entity = new Group();
    $entity->setKeyname($keyname)->setName($keyname);
    $this->groups->update($entity);

    return $entity;
  }

  /**
   * The next reads come from the database, not from the identity map.
   */
  public function clear(): void
  {
    $this->doctrine->clear();
  }
}
