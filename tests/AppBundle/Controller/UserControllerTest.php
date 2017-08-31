<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 30/08/17
 * Time: 23:13
 */
namespace Tests\AppBundle\Controller;

use AppBundle\Entity\User;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UserControllerTest extends WebTestCase
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

    public function testListShouldReturnAListOfUsers()
    {
        $this->login(['ROLE_ADMIN']);
        $this->client->request('GET', '/users');
        $this->assertEquals($this->client->getResponse()->getStatusCode(), 200);

        $this->assertContains('JohnDoe', $this->client->getResponse()->getContent());

    }

    public function testCreateShouldAddANewUser(){

        $this->login(['ROLE_USER']);
        $crawler = $this->client->request('GET', '/users/create');
        $form = $crawler->selectButton('Ajouter')->form();
        $form['user[username]'] = 'JaneDoe';
        $form['user[password][first]'] = 'testpass';
        $form['user[password][second]'] = 'testpass';
        $form['user[email]'] = 'janedoe@monmail.com';

        $this->client->submit($form);

        $this->assertEquals($this->client->getResponse()->getStatusCode(), 302);

        $user = $this->em->getRepository('AppBundle:User')->findOneBy(['id' => 2]);

        $this->assertEquals('JaneDoe', $user->getUsername());

    }

    public function testEditShouldUpdateAnUser(){

        $this->login(['ROLE_ADMIN']);
        $crawler = $this->client->request('GET', '/users/1/edit');
        $form = $crawler->selectButton('Modifier')->form();
        $form['user[username]'] = 'JaneDoes';
        $form['user[password][first]'] = 'testpass';
        $form['user[password][second]'] = 'testpass';
        $form['user[email]'] = 'janedoes@monmail.com';

        $this->client->submit($form);

        $this->assertEquals($this->client->getResponse()->getStatusCode(), 302);

        $user = $this->em->getRepository('AppBundle:User')->findOneBy(['id' => 1]);

        $this->assertEquals('JaneDoes', $user->getUsername());

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
