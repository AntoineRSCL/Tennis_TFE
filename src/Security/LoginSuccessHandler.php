<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\Response;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private $router;
    private $requestStack;

    public function __construct(RouterInterface $router, RequestStack $requestStack)
    {
        $this->router = $router;
        $this->requestStack = $requestStack;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        // Récupérer l'URL de redirection depuis le champ caché du formulaire
        $redirectUrl = $request->get('redirect');

        // Si aucune URL de redirection n'est trouvée, utiliser celle de la session ou la redirection par défaut
        if (!$redirectUrl) {
            $session = $this->requestStack->getSession();
            $redirectUrl = $session->get('_security.main.target_path'); // ou '_security.{firewall}.target_path'
        }

        // Si toujours aucune URL, utiliser l'index par défaut
        if (!$redirectUrl) {
            $redirectUrl = $this->router->generate('myclub_index');
        }

        return new RedirectResponse($redirectUrl);
    }
}
