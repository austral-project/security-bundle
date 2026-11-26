# Austral Security

[![Version](https://img.shields.io/packagist/v/austral/security-bundle?label=version)](https://packagist.org/packages/austral/security-bundle)
[![CI](https://github.com/austral-project/security-bundle/actions/workflows/ci.yml/badge.svg?branch=4.x)](https://github.com/austral-project/security-bundle/actions/workflows/ci.yml)
[![PHP](https://img.shields.io/packagist/dependency-v/austral/security-bundle/php?version=4.x-dev)](https://packagist.org/packages/austral/security-bundle)
[![Symfony](https://img.shields.io/badge/symfony-7.4-black)](https://symfony.com/releases/7.4)
[![License](https://img.shields.io/github/license/austral-project/security-bundle)](LICENSE)

Austral users, roles and user groups

> This bundle is part of [Austral](https://github.com/austral-project), a modular back office and CMS for Symfony.

## Requirements

- PHP 8.4 or higher
- Symfony 7.4
- Doctrine ORM 3

## Installation

```bash
composer require austral/security-bundle
```

If you do not use Symfony Flex, register the bundle in `config/bundles.php`:

```php
return [
    // ...
    Austral\SecurityBundle\AustralSecurityBundle::class => ['all' => true],
];
```

The entities of the application extend the mapped superclasses of the bundle: copy them from `src/Skeleton/Entity`
to `src/Entity/Austral/SecurityBundle` (the entity-bundle initializer does it).

The firewall belongs to the application: the bundle gives the user provider, the user checker and the voter, the
application declares them in its `config/packages/security.yaml` (admin-bundle documents its firewall):

```yaml
security:
    role_hierarchy:
        ROLE_ROOT: [ ROLE_ADMIN_ACCESS, ROLE_USER_ACCESS ]

    password_hashers:
        App\Entity\Austral\SecurityBundle\User: 'auto'

    providers:
        austral_security_user:
            id: austral.security.user_provider

    firewalls:
        main:                                   # the firewall of the application
            provider:       austral_security_user
            user_checker:   austral.security.user_checker
            form_login:
                enable_csrf: true
            login_link:                         # optional, for the login link
                check_route:            app_login_link_check
                signature_properties:   [ id, email, password, isActive ]
                lifetime:               600
                max_uses:               1
                check_post_only:        true    # mail scanners do not use the link up
```

## Configuration

```yaml
# config/packages/austral_security.yaml
austral_security:
    entities:                   # classes of the application (the Doctrine associations are resolved to them)
        user:   App\Entity\Austral\SecurityBundle\User
        role:   App\Entity\Austral\SecurityBundle\Role
        group:  App\Entity\Austral\SecurityBundle\Group
    password_reset:
        lifetime:           3600  # seconds a reset link is valid (60 minimum)
        request_interval:   60    # seconds before a new link can be requested for the same user
    login_link:
        firewall:           ~     # firewall of security.yaml with "login_link"; no login link when not set
```

The lifetime and the number of uses of a login link are set in `security.yaml`, under the `login_link` of the
firewall.

## Usage

### Users, roles and groups

A user has a type (`root`, `admin` or `user`, giving `ROLE_ROOT`, `ROLE_ADMIN_ACCESS` or `ROLE_USER_ACCESS`), its
own roles and the roles of its groups. A root user has all the rights: the `RootVoter` grants every attribute.

```php
$users = $container->get('austral.entity_manager.user');

$user = $users->create(['email' => 'jane@austral.dev', 'username' => 'jane']);
$user->setPlainPassword('secret')->setIsActive(true)->setTypeUser(UserInterface::USER_TYPE_ADMIN);
$users->updateUser($user); // the plain password is hashed with the hasher of security.yaml, then cleared
```

From the command line (the password is asked, hidden, when `--password` is not given):

```bash
php bin/console austral:security:user --email=support@austral.dev --root --create --if-not-exist
php bin/console austral:security:user --email=jane@austral.dev   # new password of a user
```

The login is the email (case insensitive) or the username. A disabled user cannot log in, and a user whose
password changes or who is disabled is logged out at its next request.

### Password reset

The screens are those of admin-bundle; the bundle gives the mechanism. The link holds a random token: only its
hash is saved, it expires, it is used once, and nothing tells whether a login exists.

```php
$passwordReset = $container->get('austral.security.password_reset');

// "Forgot password" form: dispatches SecurityEvents::PASSWORD_RESET_REQUESTED with the link
$passwordReset->request($login, fn (string $token): string => $urlGenerator->generate('app_reset', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL));

// Reset page
$user = $passwordReset->verify($token);           // null: invalid or expired link
$passwordReset->reset($token, $newPlainPassword); // dispatches SecurityEvents::PASSWORD_RESET
```

Send the email in a listener:

```php
#[AsEventListener(SecurityEvents::PASSWORD_RESET_REQUESTED)]
public function onPasswordResetRequested(PasswordResetRequestedEvent $event): void
{
    // $event->getUser(), $event->getUrl(), $event->getExpiresAt()
}
```

### Login link

With `login_link` in the firewall and `austral_security.login_link.firewall` set:

```php
$container->get('austral.security.login_link')->request($login, $request);
// dispatches SecurityEvents::LOGIN_LINK_REQUESTED (LoginLinkRequestedEvent: user, URL, expiry)
```

### Back office (admin-bundle)

With admin-bundle, the bridge `src/Bridge/Admin` adds the modules `security/admin-users`, `security/users`,
`security/groups` and `security/roles` (their Admin classes and `Bridge/Admin/austral_admin.yaml`). The bridge
is loaded only when `AustralAdminBundle` is enabled; the login pages and "my account" are in admin-bundle.

- **Administrators** (types admin and root) and **users** (type user): list, filters, export CSV / JSON, the
  switch "enabled" in the list.
- **Groups**: the roles of the admin modules as switches, by module; a role missing in the database is created
  when it is given.
- **Roles**: the roles saved in the database.

A user who is not root does not see the root users, does not give the type root, gives only the groups and the
roles he has (the groups he cannot give stay on the account), and does not turn a role into another one; nobody
disables or deletes his own account. The roles of the modules are those of 3.1 (`ROLE_SECURITY_ADMIN_USERS`,
`ROLE_SECURITY_USERS_EDIT`...).

The application changes or disables the modules in `config/packages/austral_admin.yaml`:

```yaml
austral_admin:
    modules:
        security:
            children:
                users: { enabled: false }
                admin-user: { downloadFormats: [ csv ] }
```

### Services

| Service | |
|---|---|
| `austral.entity_manager.user`, `.role`, `.group` | entity managers |
| `austral.security.user_provider` | user provider of `security.yaml` |
| `austral.security.user_checker` | user checker of `security.yaml` |
| `austral.security.password_updater` | hashes the plain password of a user |
| `austral.security.password_reset` | `PasswordResetManager` |
| `austral.security.login_link` | `LoginLinkRequester` |

## Documentation

The full documentation is available at https://austral.dev.

## Versions

| Bundle | Symfony | PHP | Branch |
|--------|---------|-----|---------|
| 4.x    | 7.4     | ≥ 8.4 | `4.x` |
| 3.1    | 5.4 / 6.4 | ≥ 8.0 | `3.1` (maintenance) |

See the [CHANGELOG](CHANGELOG.md) and, to upgrade from 3.1, [UPGRADE-4.0](UPGRADE-4.0.md).

## Contributing

Contributions are welcome: see the [contributing guide](https://github.com/austral-project/.github/blob/master/CONTRIBUTING.md). To report a security issue, follow the [security policy](https://github.com/austral-project/.github/blob/master/SECURITY.md).

## License

MIT, see [LICENSE](LICENSE).

## Credits

Created by [Matthieu Beurel](https://www.mbeurel.com). Sponsored by [Yipikai Studio](https://yipikai.studio).
