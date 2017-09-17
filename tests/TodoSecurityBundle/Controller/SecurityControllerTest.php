<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 30/08/17
 * Time: 23:31
 */
namespace Tests\AppBundle\Controller;

use TodoSecurityBundle\Entity\User;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class SecurityControllerTest extends WebTestCase
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

    public function testLoginShouldRedirectToIndex()
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username'] = 'JohnDoe';
        $form['_password'] = 'A45y22s@';

        $this->client->submit($form);

        $this->assertEquals($this->client->getResponse()->getStatusCode(), 302);

    }

    public function testLogoutValidatorActionShouldRedirectToIndex(){

        $crawler = $this->client->request('GET', '/logoutvalidator');
        $this->assertEquals($this->client->getResponse()->getStatusCode(), 302);

    }

    protected function tearDown()
    {
        $this->em->close();
        $this->em = NULL;
    }

}
