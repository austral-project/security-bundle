<?php
/*
 * This file is part of the Austral Security Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\SecurityBundle\Command;

use Austral\SecurityBundle\Entity\Interfaces\UserInterface;
use Austral\SecurityBundle\Entity\User;
use Austral\SecurityBundle\EntityManager\UserEntityManager;

use Austral\ToolsBundle\Command\Base\Command;
use Austral\ToolsBundle\Command\Exception\CommandException;


use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Austral User Command.
 * @template T of object
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class UserCommand extends Command
{

  /**
   * @var string
   */
  protected static $defaultName = 'austral:security:user';

  /**
   * @var string
   */
  protected string $titleCommande = "Create or update User in project";

  /**
   * {@inheritdoc}
   */
  protected function configure(): void
  {
    $this
      ->setDefinition([
        new InputOption('--username', '-u', InputOption::VALUE_REQUIRED, 'Username to user'),
        new InputOption('--email', '', InputOption::VALUE_REQUIRED, 'Email to user'),
        new InputOption('--password', '-p', InputOption::VALUE_REQUIRED, 'Password to user'),
        new InputOption('--root', '', InputOption::VALUE_NONE, 'Setted Super Admin to user role '),
        new InputOption('--create', '-c', InputOption::VALUE_NONE, 'Used to your create a new user'),
        new InputOption('--disabled', '-d', InputOption::VALUE_NONE, 'Disabled user'),
        new InputOption('--if-not-exist', '', InputOption::VALUE_NONE, 'Disabled user'),
      ])
      ->setDescription('Create new User or update password user')
      ->setHelp(<<<'EOF'
The <info>%command.name%</info> command to create or update password user

  <info>php %command.full_name% --email support@austral.dev --username=username --password=your-password --create</info>
  <info>php %command.full_name% --email support@austral.dev --username=username --password=your-password</info>
  
  <info>php %command.full_name% --email support@austral.dev -u username -p your-password -c</info>
  <info>php %command.full_name% --email support@austral.dev -u username -p your-password</info>
EOF
      )
    ;
  }

  /**
   * @param UserEntityManager<T> $userEntityManager
   * @param string|null $name
   * @param (callable(): mixed)|null $code
   */
  public function __construct(
    #[Autowire(service: 'austral.entity_manager.user')] protected UserEntityManager $userEntityManager,
    ?string $name = null,
    ?callable $code = null)
  {
    parent::__construct($name, $code);
  }

  /**
   * @param InputInterface $input
   * @param OutputInterface $output
   *
   * @return void
   * @throws CommandException
   * @throws NonUniqueResultException
   */
  protected function executeCommand(InputInterface $input, OutputInterface $output): void
  {
    $createUser = $input->getOption("create");
    if(!$email = $input->getOption('email'))
    {
      throw new CommandException("Please defined user email !");
    }
    if(!$password = $input->getOption('password'))
    {
      throw new CommandException("Please defined user password !");
    }
    $username = $input->getOption('username') ? : $email;

    /** @var UserInterface<T>|null $user */
    $user = $this->userEntityManager->retrieveByEmail($email);
    if($createUser && $user)
    {
      if(!$input->getOption("if-not-exist"))
      {
        throw new CommandException("you don't create user with username {$email} by this user already exist !");
      }
      else
      {
        $this->viewMessage("User {$email} is already exist !!!", "success");
        return ;
      }
    }
    elseif($user)
    {
      $user->setPlainPassword($password);
    }
    else
    {
      /** @var UserInterface<T> $user */
      $user = $this->userEntityManager->create(array(
          "username"        =>  $username,
          "email"           =>  $email,
          "plainPassword"   =>  $password,
          "firstname"       =>  "Firstname",
          "lastname"        =>  "Lastname",
        )
      );
    }
    if($input->getOption('root'))
    {
      $user->setTypeUser(UserInterface::USER_TYPE_ROOT);
    }

    if($input->getOption('disabled'))
    {
      $user->setIsActive(false);
    }
    else
    {
      $user->setIsActive(true);
    }

    $this->viewMessage("User {$email} is ".($createUser ? "created" : "updated")." successfully !!!", "success");
    $this->userEntityManager->updateUser($user);
  }



}