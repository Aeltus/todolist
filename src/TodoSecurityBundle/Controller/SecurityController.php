<?php

namespace TodoSecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class SecurityController extends Controller
{

    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $response = $this->render('security/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
        $response->setSharedMaxAge(15);
        $response->headers->addCacheControlDirective('must-revalidate', true);

        return $response;
    }

    public function logoutValidatorAction(Response $response = NULL)
    {
        if($response){
            $response->expire();
        }

        return $this->redirectToRoute('homepage');
    }

}
