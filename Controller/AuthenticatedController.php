<?php
/*
 * This file is part of the Austral Security Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\SecurityBundle\Controller;

use Austral\HttpBundle\Controller\HttpController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Austral Authenticated Controller.
 * @template T of object
 *
 * @extends HttpController<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 *
 * @final
 */
class AuthenticatedController extends HttpController
{

  /**
   * login
   *
   * @param AuthenticationUtils $authenticationUtils
   * @param Request $request
   * @return RedirectResponse|Response
   * @throws \Twig\Error\LoaderError
   * @throws \Twig\Error\RuntimeError
   * @throws \Twig\Error\SyntaxError
   */
  public function login(AuthenticationUtils $authenticationUtils, Request $request): RedirectResponse|Response
  {
    if ($this->getUser())
    {
      $redirectRouteName = $this->container->getParameter("security_login_redirect");
      $redirectRouteName = is_string($redirectRouteName) ? $redirectRouteName : "austral_admin_dashboard";
      return new RedirectResponse($this->generateUrl($redirectRouteName), 302);
    }
    $error = $authenticationUtils->getLastAuthenticationError();
    $lastUsername = $authenticationUtils->getLastUsername();
    return $this->render('@AustralSecurity/login.html.twig', array(
        "last_username" => $lastUsername,
        "error"         => $error
      )
    );
  }

  /**
   * Logout is blank method
   */
  public function logout(): void
  {
    throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
  }

}


