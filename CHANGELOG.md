# Changelog

All notable changes to this bundle are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html). All Austral bundles share the same major and minor version.

## [Unreleased]

The bundle is rewritten for 4.0: it keeps the users, roles and groups, the user provider and the password
mechanisms; the login page and the screens move to admin-bundle, the admin modules to the bridge
`Bridge/Admin`. See UPGRADE-4.0.

### Added

- Configuration `austral_security`: `entities` (classes of the application), `password_reset.lifetime` and
  `password_reset.request_interval`, `login_link.firewall`
- `PasswordResetManager` (`austral.security.password_reset`): reset link with a selector and a verifier (only
  the SHA-256 hash of the verifier is saved), expiry, one use, one link per request interval, no user
  enumeration; events `SecurityEvents::PASSWORD_RESET_REQUESTED` and `SecurityEvents::PASSWORD_RESET`
- `LoginLinkRequester` (`austral.security.login_link`): login link of the firewall (Symfony `login_link`),
  event `SecurityEvents::LOGIN_LINK_REQUESTED`
- Bridge with admin-bundle (`Bridge/Admin`, loaded when `AustralAdminBundle` is enabled): the modules of the
  administrators, users, groups and roles on the admin-bundle 4 API, POST + CSRF actions, the rights of a user
  who is not root restricted (root users hidden, type root, groups and roles he does not have)
- `RootVoter`: a root user has all the rights
- `UserChecker` (`austral.security.user_checker`): a disabled user cannot log in
- `PasswordUpdater` (`austral.security.password_updater`) and `PasswordListener`: the plain password is hashed
  when the user is saved, then cleared
- Columns `password_reset_selector`, `password_reset_hash`, `password_reset_expires_at` on the user
- `User::isEqualTo()`: the user is logged out when its password changes, when it is disabled or when its
  identifier changes; the session keeps neither the password hash nor the reset token
- `austral:security:user`: option `--admin`, the password is asked (hidden) when `--password` is not given
- README: the `security.yaml` of the application for Symfony 7.4 (provider, user checker, hasher, login link)
- Tests (entities, repositories on SQLite, provider, checker, voter, password, reset, login link, command,
  configuration, container, skeleton)

### Changed

- Sources in `src/`, PHP 8.4, Symfony 7.4, Doctrine ORM 3; explicit services (no autowiring), public aliases
  `austral.*` only
- The join column of `austral_security_user_group` is `group_id` (it was `role_id`)
- Passwords use the hasher `auto` of `security.yaml`, without salt; the hash is upgraded at login
  (`UserProvider::upgradePassword()`)
- `UserProvider` loads a user by email, username or id, and refreshes it by id
- The ids are generated with `symfony/uid` (UUID v4)
- `RoleRepository::selectRoles()` / `RoleEntityManager::selectRoles()` return an array indexed by role
- `UserEntityManagerInterface` extends `EntityManagerORMInterface` only
- The skeleton entities are valid PHP files
- `getTypeUser()` returns a string, `setTypeUser()` refuses an unknown type

### Fixed

- Passwords hashed as `password@salt`: no user created in 4.x could log in
- `refreshUser()` raised an error for a disabled user (500 error), reloaded by username and threw
  `UnsupportedUserException` for a deleted user
- `UserEntityManagerInterface` extended two incompatible interfaces (fatal error with entity-bundle 4.x)
- `User` implemented `EntityFileBundle\Entity\Interfaces\CropperInterface`, removed from entity-file-bundle
  4.x (fatal error)
- `RoleRepository::selectRoles()` returned an array where a `Collection` was declared
- `retrieveByLogin()` hid the database errors
- The forgotten password token was saved in plain text
- Undeclared dependencies (ramsey/uuid, admin, list, filter and form bundles); `config/bundles.php` read by the
  extension

### Removed

- `MigratePasswordHasher` (`austral.security.hasher.migrate_password`) and the `salt` column
- The `forgot_password_token` column and `get/setForgotPasswordToken()`
- The override of `security.authorization_checker` (`Security\Authorization\AuthorizationChecker`)
- `DoctrineResolveTargetEntityPass` and the parameter `austral.resolve_target_entities.security`: the bundle
  prepends `doctrine.orm.resolve_target_entities`
- `EntityManagerListener` (replaced by `PasswordListener`), `BaseUserEntityRepository`
- `UserInterface::getSalt()` / `setSalt()`, `UserEntityManager::updatePassword()`, `deleteUser()` and
  `setPasswordHasherFactory()`
- Moved to admin-bundle: `UserForm`, `DashboardListener`, `AuthenticatedController`, `routes.yaml` and the
  views (login, layout, avatar)
- `Admin\UserAdmin`, `AdminUserAdmin`, `GroupAdmin`, `RoleAdmin`, `Resources/config/austral_admin.yaml`: moved to
  `Bridge\Admin`
- The skeleton `security.yaml` (`Resources/config/squelette`): the firewall belongs to the application, the
  one of the back office is documented by admin-bundle
- Dependencies: austral/tools-bundle, ramsey/uuid, ext-sodium, symfony/framework-bundle

## [3.1.1] - 2022-12-16

- Remove deprecated in compiler doctrine
- Add french translate
- Fix deprecated functions for security compatibility from symfony 5.4

## [3.1.0] - 2022-09-17

- Updated bundles version to 3.1 for use the new Entities Interfaces

## [3.0.0] - 2021-02-10

- Created new repository and new branch in to Github
- Changed the project License to the MIT
- Writing and publish documentation
- Add to Packagist Service for installation with composer

## 2.x and 1.x

These versions were not open source and have no public documentation.

[Unreleased]: https://github.com/austral-project/security-bundle/compare/v3.1.3...4.x
[3.1.1]: https://github.com/austral-project/security-bundle/compare/v3.1.0...v3.1.1
[3.1.0]: https://github.com/austral-project/security-bundle/compare/v3.0.0...v3.1.0
[3.0.0]: https://github.com/austral-project/security-bundle/releases/tag/v3.0.0
