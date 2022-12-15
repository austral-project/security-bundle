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
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 *
 * @final
 */
class AuthenticatedController extends HttpController
{

  /**
   * @param Request $request
   *
   * @return Response
   */
  public function login(AuthenticationUtils $authenticationUtils, Request $request)
  {
    if ($this->getUser())
    {
      return new RedirectResponse($this->generateUrl($this->container->getParameter("security_login_redirect"), array()), 302);
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
  public function logout()
  {
    throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
  }

}


