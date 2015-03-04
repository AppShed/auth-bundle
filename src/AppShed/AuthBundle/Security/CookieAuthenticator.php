<?php
/**
 * Created by PhpStorm.
 * User: Vitaliy Pitvalo
 * Date: 2/27/15
 * Time: 1:53 PM
 */

namespace AppShed\AuthBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

class CookieAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{
    private $userProvider;
    private $cookieName;


    public function __construct(CookieUserProvider $userProvider, $cookieName)
    {
        $this->cookieName = $cookieName;
        $this->userProvider = $userProvider;
    }

    public function createToken(Request $request, $providerKey)
    {
        $sessionKey = $request->cookies->get($this->cookieName);

        if (!$sessionKey) {
            throw new BadCredentialsException('No session found');
        }

        return new PreAuthenticatedToken(
            'anon.',
            $sessionKey,
            $providerKey
        );
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        $user = $this->userProvider->loadUserForSession($token->getCredentials());

        return new PreAuthenticatedToken(
            $user,
            $token->getCredentials(),
            $providerKey,
            $user->getRoles()
        );
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }

    /**
     * This is called when an interactive authentication attempt fails. This is
     * called by authentication listeners inheriting from
     * AbstractAuthenticationListener.
     *
     * @param Request $request
     * @param AuthenticationException $exception
     *
     * @return Response The response to return, never null
     */
    public function onAuthenticationFailure( Request $request, AuthenticationException $exception )
    {
        return new Response('', 403);
    }
}