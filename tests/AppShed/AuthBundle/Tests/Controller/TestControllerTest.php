<?php

/**
 * Created by PhpStorm.
 * User: Vitaliy Pitvalo
 * Date: 2/26/15
 * Time: 2:31 PM
 */

namespace AppShedAuthBundle\Tests\Controller;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\Request;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

class TestControllerTest extends WebTestCase
{

    public function testSuccess()
    {
        $client  = static::createClient();
        $options = $client->getContainer()->getParameter('app_shed_auth.cookie_name');

        $mockClient = $this->getMock('GuzzleHttp\Client', ['get'], [], '', false);
        $mockClient->expects($this->once())
                   ->method('get')
                   ->with($this->anything(), $this->callback(function ($ops) {
                       return is_array($ops) && array_key_exists('query',
                           $ops) && is_array($ops['query']) && array_key_exists('sessionId',
                           $ops['query']) && $ops['query']['sessionId'] == 'qweqw';
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

        $client->getContainer()->set('app_shed_auth.client', $mockClient);
        $client->getCookieJar()->set(new Cookie($options, 'qweqw'));

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
        $client  = static::createClient();
        $options = $client->getContainer()->getParameter('app_shed_auth.cookie_name');

        $mockClient = $this->getMock('GuzzleHttp\Client', ['get'], [], '', false);
        $mockClient->expects($this->once())
                   ->method('get')
                   ->will($this->throwException(new RequestException("Not Found", new Request('GET', ''),
                       new Response(404, ['Content-type' => 'application/json']))));

        $client->getContainer()->set('app_shed_auth.client', $mockClient);
        $client->getCookieJar()->set(new Cookie($options, 'qweqwq'));

        $client->request('GET', '/');

        $response = $client->getResponse();
        $this->assertSame(403, $response->getStatusCode());
    }


}
