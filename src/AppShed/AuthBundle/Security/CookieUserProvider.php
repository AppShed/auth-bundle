<?php
/**
 * Created by PhpStorm.
 * User: Vitaliy Pitvalo
 * Date: 2/26/15
 * Time: 2:31 PM
 */


namespace AppShed\AuthBundle\Security;

use AppShed\AuthBundle\User\User;
use Doctrine\Common\Cache\Cache;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class CookieUserProvider implements UserProviderInterface
{

    const URL_COOKIE = '/user/session';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Cache
     */
    private $cache;

    public function __construct(Client $client, Cache $cache = null)
    {
        $this->client = $client;
        $this->cache = $cache;
    }

    public function loadUserForSession($sessionKey)
    {
        if ($this->cache && ($user = $this->cache->fetch($sessionKey))) {
            return $user;
        }

        try {
            $response = $this->client->get(self::URL_COOKIE,
                [
                    'query' => ['sessionId' => $sessionKey]
                ]
            );

            $user = new User($response->json());
            if ($this->cache) {
                $this->cache->save( $sessionKey, $user );
            }

            return $user;
        } catch (RequestException $e) {
            if ($e->getCode() == 404) {
                throw new UsernameNotFoundException('User not found', $e->getCode(), $e);
            } elseif ($e->getCode() == 401) {
                throw new UnsupportedUserException('User not authorized', $e->getCode(), $e);
            } else {
                throw new AuthenticationServiceException("Server problem", $e->getCode(), $e);
            }
        }
    }

    public function loadUserByUsername($username)
    {
        throw new UsernameNotFoundException("Provider doesnt support loading by username");
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException();
        }
        return $user;
    }

    public function supportsClass($class)
    {
        return 'AppShed\AuthBundle\User\User' === $class;
    }
}