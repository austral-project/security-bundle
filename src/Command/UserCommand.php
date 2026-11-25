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
use Austral\SecurityBundle\EntityManager\Interfaces\UserEntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Austral User Command: creates a user or updates its password, its type and its status.
 *
 * Without --password, the password is asked (hidden): it stays out of the shell history.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class UserCommand extends Command
{
  public const string NAME = 'austral:security:user';

  /**
   * @param UserEntityManagerInterface $users
   */
  public function __construct(private readonly UserEntityManagerInterface $users)
  {
    parent::__construct(self::NAME);
  }

  /**
   * @return void
   */
  protected function configure(): void
  {
    $this->setDescription('Create a user or update its password')
      ->addOption('email', null, InputOption::VALUE_REQUIRED, 'Email of the user')
      ->addOption('username', 'u', InputOption::VALUE_REQUIRED, 'Username of a new user (the email by default)')
      ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'Password (asked, hidden, when not given)')
      ->addOption('root', null, InputOption::VALUE_NONE, 'The user is root (all the rights)')
      ->addOption('admin', null, InputOption::VALUE_NONE, 'The user is an administrator')
      ->addOption('create', 'c', InputOption::VALUE_NONE, 'The user must be new: an error when the email exists')
      ->addOption('if-not-exist', null, InputOption::VALUE_NONE, 'With --create: do nothing when the email exists')
      ->addOption('disabled', 'd', InputOption::VALUE_NONE, 'Disable the user (enabled otherwise)')
      ->setHelp(<<<'HELP'
        The <info>%command.name%</info> command creates the user of an email, or updates its password:

          <info>php %command.full_name% --email=support@austral.dev --root --create</info>
          <info>php %command.full_name% --email=support@austral.dev</info>

        Without <comment>--password</comment>, the password is asked and not displayed.
        HELP);
  }

  /**
   * @param InputInterface  $input
   * @param OutputInterface $output
   *
   * @return int
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $email = trim((string) $input->getOption('email'));
    if ('' === $email)
    {
      $io->error('The option --email is required.');

      return self::INVALID;
    }
    if ($input->getOption('root') && $input->getOption('admin'))
    {
      $io->error('Choose --root or --admin, not both.');

      return self::INVALID;
    }

    $user = $this->users->retrieveByEmail($email);
    $create = (bool) $input->getOption('create');
    if ($create && null !== $user)
    {
      if ($input->getOption('if-not-exist'))
      {
        $io->success("The user {$email} already exists.");

        return self::SUCCESS;
      }
      $io->error("The user {$email} already exists.");

      return self::FAILURE;
    }

    $password = $input->getOption('password');
    if (!\is_string($password))
    {
      $question = new Question('Password')
        ->setHidden(true)
        ->setMaxAttempts(3)
        ->setValidator(static function (mixed $value): string {
          if (!\is_string($value) || '' === $value)
          {
            throw new \InvalidArgumentException('The password cannot be empty.');
          }

          return $value;
        });
      $password = $io->askQuestion($question);
    }

    $created = null === $user;
    if (null === $user)
    {
      $username = $input->getOption('username');
      $user = $this->users->create([
        'username' => \is_string($username) && '' !== $username ? $username : $email,
        'email' => $email,
        'firstname' => 'Firstname',
        'lastname' => 'Lastname',
      ]);
    }
    $user->setPlainPassword((string) $password);
    if ($input->getOption('root'))
    {
      $user->setTypeUser(UserInterface::USER_TYPE_ROOT);
    }
    elseif ($input->getOption('admin'))
    {
      $user->setTypeUser(UserInterface::USER_TYPE_ADMIN);
    }
    $user->setIsActive(!$input->getOption('disabled'));
    $this->users->updateUser($user);

    $io->success(\sprintf('The user %s is %s.', $email, $created ? 'created' : 'updated'));

    return self::SUCCESS;
  }
}
