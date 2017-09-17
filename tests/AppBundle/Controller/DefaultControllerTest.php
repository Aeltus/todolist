<?php

namespace Tests\AppBundle\Controller;

use TodoSecurityBundle\Entity\User;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class DefaultControllerTest extends WebTestCase
{

    protected $em;
    protected $user;
    protected $client;
    protected $container;

    protected function setUp(){

        $this->user = new User();
        $this->user->setEmail('john.doe@monmail.com');
        $this->user->setUsername('JohnDoe');
        $this->user->setPassword('$2y$13$JupoD6T7C6wzIaXCQoZyK.GIWG9MO9jwgS9iMgYKy788aG7.Tyd92');

        $this->client = static::createClient();
        $this->container = $this->client->getContainer();
        $this->em = $this->container->get('doctrine')->getManager();

        static $metadatas;

        if(! isset($metadatas)){
            $metadatas = $this->em->getMetadataFactory()->getAllMetadata();
        }


        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropDatabase();

        if(! empty($metadatas)){
            $schemaTool->createSchema($metadatas);
        }


        $this->em->persist($this->user);
        $this->em->flush();

    }

    public function testUnidentifiedIndexShouldRedirectToLogin()
    {
        $this->client->request('GET', '/');
        $this->assertEquals($this->client->getResponse()->getStatusCode(), 302);

    }

    public function testIdentifiedIndexShouldBeOk(){

        $this->login(['ROLE_USER']);
        $this->client->request('GET', '/');
        $this->assertEquals($this->client->getResponse()->getStatusCode(), 200);

    }

    private function login($roles){
        $session = $this->container->get('session');

        // the firewall context defaults to the firewall name
        $firewallContext = 'main';

        $token = new UsernamePasswordToken('JohnDoe', null, $firewallContext, $roles);
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    protected function tearDown()
    {
        $this->em->close();
        $this->em = NULL;
    }

}
