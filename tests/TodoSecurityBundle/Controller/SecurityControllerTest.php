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

        $session = $this->container->get('session');

        // the firewall context defaults to the firewall name
        $firewallContext = 'main';

        $token = new UsernamePasswordToken($this->user, null, $firewallContext, ['ROLE_USER']);
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);

        $crawler = $this->client->request('GET', '/logoutvalidator');
        $this->assertEquals($this->client->getResponse()->getStatusCode(), 302);
        $this->client->followRedirect();
        $this->assertEquals($this->client->getResponse()->getStatusCode(), 200);

    }

    public function testLogoutShouldRedirectToLogin(){
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username'] = 'JohnDoe';
        $form['_password'] = 'A45y22s@';

        $this->client->submit($form);

        $crawler = $this->client->followRedirect();

        $disconnect = $crawler->selectLink('Se déconnecter')->link();
        $crawler = $this->client->click($disconnect);

        $this->client->followRedirect();
        $this->assertEquals($this->client->getResponse()->getStatusCode(), 302);

        $this->client->followRedirect();
        $this->assertEquals($this->client->getResponse()->getStatusCode(), 200);

        $this->assertContains('Nom d\'utilisateur', $this->client->getResponse()->getContent());

    }

    protected function tearDown()
    {
        $this->em->close();
        $this->em = NULL;
    }

}
