<?php
/*
 * This file is part of the Austral Security Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\SecurityBundle\Security\Authenticator;

use Austral\SecurityBundle\EntityManager\Interfaces\UserEntityManagerInterface;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * Austral User Authenticator.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class UserAuthenticator extends AbstractFormLoginAuthenticator implements PasswordAuthenticatedInterface
{
  use TargetPathTrait;

  /**
   * @var UserEntityManagerInterface
   */
  private UserEntityManagerInterface $userManager;

  /**
   * @var UrlGeneratorInterface
   */
  private UrlGeneratorInterface $urlGenerator;

  /**
   * @var CsrfTokenManagerInterface
   */
  private CsrfTokenManagerInterface $csrfTokenManager;

  /**
   * @var UserPasswordEncoderInterface
   */
  private UserPasswordEncoderInterface $passwordEncoder;

  /**
   * @var SessionInterface
   */
  private SessionInterface $session;

  /**
   * LoginAuthenticator constructor.
   *
   * @param UserEntityManagerInterface $userManager
   * @param UrlGeneratorInterface $urlGenerator
   * @param CsrfTokenManagerInterface $csrfTokenManager
   * @param UserPasswordEncoderInterface $passwordEncoder
   * @param SessionInterface $session
   */
  public function __construct(UserEntityManagerInterface $userManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder, SessionInterface $session)
  {
    $this->userManager = $userManager;
    $this->urlGenerator = $urlGenerator;
    $this->csrfTokenManager = $csrfTokenManager;
    $this->passwordEncoder = $passwordEncoder;
    $this->session = $session;
  }

  /**
   * @param Request $request
   *
   * @return bool
   */
  public function supports(Request $request): bool
  {
    return strpos($request->attributes->get('_route'), "security_login") !== false && $request->isMethod('POST');
  }

  /**
   * @param Request $request
   *
   * @return array
   */
  public function getCredentials(Request $request): array
  {
    $credentials = [
      'login'       => $request->request->get('login'),
      'password'    => $request->request->get('password'),
      'csrf_token'  => $request->request->get('_csrf_token'),
    ];
    $request->getSession()->set(
      Security::LAST_USERNAME,
      $credentials['login']
    );
    return $credentials;
  }

  /**
   * @param mixed $credentials
   * @param UserProviderInterface $userProvider
   *
   * @return UserInterface|null
   */
  public function getUser($credentials, UserProviderInterface $userProvider): ?UserInterface
  {
    $token = new CsrfToken('authenticate', $credentials['csrf_token']);
    if (!$this->csrfTokenManager->isTokenValid($token)) {
      throw new InvalidCsrfTokenException();
    }
    /** @var \Austral\SecurityBundle\Entity\Interfaces\UserInterface $user */
    if (!$user = $this->userManager->retreiveByLogin($credentials['login'])) {
      // fail authentication with a custom error
      throw new CustomUserMessageAuthenticationException('User is not found');
    }
    if (!$user->getIsActive()) {
      // fail authentication with a custom error
      throw new CustomUserMessageAuthenticationException('User is blocked');
    }
    return $user;
  }

  /**
   * @param mixed $credentials
   * @param UserInterface $user
   *
   * @return bool
   */
  public function checkCredentials($credentials, UserInterface $user): bool
  {
    return $this->passwordEncoder->isPasswordValid($user, $credentials['password'].$user->getSalt());
  }

  /**
   * @param mixed $credentials
   *
   * @return string|null
   */
  public function getPassword($credentials): ?string
  {
    return $credentials['password'];
  }

  /**
   * @param Request $request
   * @param TokenInterface $token
   * @param string $providerKey
   *
   * @return RedirectResponse
   */
  public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey): RedirectResponse
  {
    if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
      return new RedirectResponse($targetPath);
    }
    $this->session->getFlashBag()->add('success', 'You are connected !');
    $referer = $request->headers->get('referer');
    $this->session->set("austral_language_interface", $token->getUser()->getLanguage());
    // TODO : Redirect on authenticatedSuccess
    return new RedirectResponse($referer ? : "/");
  }

  /**
   * @return string
   */
  protected function getLoginUrl(): string
  {
    return $this->urlGenerator->generate("austral_admin_security_login");
  }
}
