<?php

/**
 * Created by PhpStorm.
 * User: Vitaliy Pitvalo
 * Date: 2/26/15
 * Time: 2:31 PM
 */

namespace AppShedAuthBundle\Tests\Controller;

use AppShed\AuthBundle\User\User;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\Request;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

class TestControllerTest extends WebTestCase
{


    private $cookieName;

    public function setUp()
    {
        $client           = static::createClient();
        $this->cookieName = $client->getContainer()->getParameter('app_shed_auth.cookie_name');
    }

    public function testSuccess()
    {
        $client = static::createClient();

        $guzzleMock = $this->getMock('GuzzleHttp\Client', ['get'], [], '', false);
        $guzzleMock->expects($this->once())
                   ->method('get')
                   ->with($this->anything(), $this->callback(function ($ops) {
                       return is_array($ops) && array_key_exists('query',
                           $ops) && is_array($ops['query']) && array_key_exists('sessionId',
                           $ops['query']) && $ops['query']['sessionId'] == 'somesessionid';
                   }))
                   ->will($this->returnValue(new Response(200, ['Content-type' => 'application/json'],
                       Stream::factory(json_encode([
                           'id'       => 10,
                           'name'     => 'name',
                           'username' => 'username',
                           'email'    => 'test@test.com',
                           'params'   => [],
                           'client'   => 10,
                           'roles'    => ['ROLE_USER']

                       ])))));

        $client->getContainer()->set('app_shed_auth.client', $guzzleMock);
        $client->getCookieJar()->set(new Cookie($this->cookieName, 'somesessionid'));

        $client->request('GET', '/');

        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $user = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('id', $user);
        $this->assertArrayHasKey('name', $user);
        $this->assertArrayHasKey('username', $user);
        $this->assertArrayHasKey('email', $user);
        $this->assertArrayHasKey('params', $user);
        $this->assertArrayHasKey('client', $user);
        $this->assertArrayHasKey('roles', $user);
    }

    public function testSessionNotFound()
    {
        $client = static::createClient();

        $guzzleMock = $this->getMock('GuzzleHttp\Client', ['get'], [], '', false);
        $guzzleMock->expects($this->once())
                   ->method('get')
                   ->will($this->throwException(new RequestException("Not Found", new Request('GET', ''),
                       new Response(404, ['Content-type' => 'application/json']))));

        $client->getContainer()->set('app_shed_auth.client', $guzzleMock);
        $client->getCookieJar()->set(new Cookie($this->cookieName, 'somesessionid'));

        $client->request('GET', '/');

        $response = $client->getResponse();
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testInvalidSession()
    {
        $client = static::createClient();

        $guzzleMock = $this->getMock('GuzzleHttp\Client', ['get'], [], '', false);
        $guzzleMock->expects($this->once())
                   ->method('get')
                   ->will($this->throwException(new RequestException("Not authorized", new Request('GET', ''),
                       new Response(401, ['Content-type' => 'application/json']))));

        $client->getContainer()->set('app_shed_auth.client', $guzzleMock);
        $client->getCookieJar()->set(new Cookie($this->cookieName, 'wrong-session-id'));

        $client->request('GET', '/');

        $response = $client->getResponse();
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testFatalError()
    {
        $client = static::createClient();

        $guzzleMock = $this->getMock('GuzzleHttp\Client', ['get'], [], '', false);
        $guzzleMock->expects($this->once())
                   ->method('get')
                   ->will($this->throwException(new RequestException("Fatal Error", new Request('GET', ''),
                       new Response(``))));

        $client->getContainer()->set('app_shed_auth.client', $guzzleMock);
        $client->getCookieJar()->set(new Cookie($this->cookieName, 'wrong-session-id'));

        $client->request('GET', '/');

        $response = $client->getResponse();
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testNoCookie()
    {
        $client     = static::createClient();
        $guzzleMock = $this->getMock('GuzzleHttp\Client', ['get'], [], '', false);
        $guzzleMock->expects($this->never())
                   ->method('get');

        $client->getContainer()->set('app_shed_auth.client', $guzzleMock);

        $client->request('GET', '/');

        $response = $client->getResponse();
        $this->assertSame(403, $response->getStatusCode());
    }


    public function testGetCache()
    {
        $client = static::createClient();

        $guzzleMock = $this->getMock('Doctrine\Common\Cache\ArrayCache', ['fetch']);
        $guzzleMock->expects($this->once())
                   ->method('fetch')
                   ->with($this->equalTo('somesessionid'))
                   ->will($this->returnValue(new User([
                       'id'       => 10,
                       'name'     => 'name',
                       'username' => 'username',
                       'email'    => 'test@test.com',
                       'params'   => [],
                       'client'   => 10,
                       'roles'    => ['ROLE_USER']

                   ])));

        $client->getContainer()->set('login_cache', $guzzleMock);
        $client->getCookieJar()->set(new Cookie($this->cookieName, 'somesessionid'));

        $client->request('GET', '/');
        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testSetCache()
    {
        $client = static::createClient();

        $cacheMock = $this->getMock('Doctrine\Common\Cache\ArrayCache', ['fetch', 'save']);
        $cacheMock->expects($this->once())
                  ->method('fetch')
                  ->will($this->returnValue(null));

        $cacheMock->expects($this->once())
                  ->method('save')
                  ->with($this->equalTo('somesessionid'), $this->callback(function ($user) {
                      return $user instanceof User;
                  }));

        $client->getContainer()->set('login_cache', $cacheMock);

        $guzzleMock = $this->getMock('GuzzleHttp\Client', ['get'], [], '', false);
        $guzzleMock->expects($this->once())
                   ->method('get')
                   ->with($this->anything(), $this->callback(function ($ops) {
                       return is_array($ops) && array_key_exists('query',
                           $ops) && is_array($ops['query']) && array_key_exists('sessionId',
                           $ops['query']) && $ops['query']['sessionId'] == 'somesessionid';
                   }))
                   ->will($this->returnValue(new Response(200, ['Content-type' => 'application/json'],
                       Stream::factory(json_encode([
                           'id'       => 10,
                           'name'     => 'name',
                           'username' => 'username',
                           'email'    => 'test@test.com',
                           'params'   => [],
                           'client'   => 10,
                           'roles'    => ['ROLE_USER']

                       ])))));

        $client->getContainer()->set('app_shed_auth.client', $guzzleMock);
        $client->getCookieJar()->set(new Cookie($this->cookieName, 'somesessionid'));


        $client->request('GET', '/');
        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
    }


}
