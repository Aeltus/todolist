<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 30/08/17
 * Time: 22:05
 */
namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Task;
use TodoSecurityBundle\Entity\User;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class TaskControllerTest extends WebTestCase
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

    public function testCreateShouldAddANewTask(){

        $this->login(['ROLE_USER']);
        $crawler = $this->client->request('POST', '/tasks/create');

        $form = $crawler->selectButton('Ajouter')->form();
        $form['task[title]'] = 'Le titre de mon test';
        $form['task[content]'] = 'Le contenu de mon test';

        $this->client->submit($form);

        $this->assertContains('Redirecting to /tasks', $this->client->getResponse()->getContent());

        $task = $this->em->getRepository('AppBundle:Task')->findOneBy(['id' => 1]);

        $this->assertEquals('Le titre de mon test', $task->getTitle());

    }

    public function testEditShouldUpdateATask(){

        $this->login(['ROLE_USER']);
        $crawler = $this->client->request('POST', '/tasks/create');
        $form = $crawler->selectButton('Ajouter')->form();
        $form['task[title]'] = 'Le titre de mon test';
        $form['task[content]'] = 'Le contenu de mon test';

        $this->client->submit($form);

        $crawler = $this->client->request('GET', '/tasks/1/edit');
        $form = $crawler->selectButton('Modifier')->form();
        $form['task[title]'] = 'Le titre de mon test modifié';
        $form['task[content]'] = 'Le contenu de mon test modifié';

        $this->client->submit($form);

        $this->assertContains('Redirecting to /tasks', $this->client->getResponse()->getContent());

        $task = $this->em->getRepository('AppBundle:Task')->findOneBy(['id' => 1]);

        $this->assertEquals('Le titre de mon test modifié', $task->getTitle());
    }

    public function testToggleShouldRedirectToTasks(){

        $this->login(['ROLE_USER']);
        $crawler = $this->client->request('GET', '/tasks/create');
        $form = $crawler->selectButton('Ajouter')->form();
        $form['task[title]'] = 'Le titre de mon test';
        $form['task[content]'] = 'Le contenu de mon test';

        $this->client->submit($form);

        $this->client->request('POST', '/tasks/1/toggle');
        $this->assertContains('Redirecting to /tasks', $this->client->getResponse()->getContent());

    }

    public function testDeleteShouldRedirectToTasks(){

        $this->login(['ROLE_USER']);
        $crawler = $this->client->request('GET', '/tasks/create');
        $form = $crawler->selectButton('Ajouter')->form();
        $form['task[title]'] = 'Le titre de mon test';
        $form['task[content]'] = 'Le contenu de mon test';

        $this->client->submit($form);

        $this->client->request('POST', '/tasks/1/delete');
        $this->assertContains('Redirecting to /tasks', $this->client->getResponse()->getContent());

    }

    public function testDeleteAnAnomynousTaskIfNotAdminShouldReturnAnErrorMessage(){

        $this->login(['ROLE_USER']);
        $crawler = $this->client->request('GET', '/tasks/create');
        $form = $crawler->selectButton('Ajouter')->form();
        $form['task[title]'] = 'Le titre de mon test';
        $form['task[content]'] = 'Le contenu de mon test';

        $this->client->submit($form);

        $task = $this->em->getRepository('AppBundle:Task')->findOneBy(['id' => 1]);
        $task->setUser();
        $this->em->flush();

        $this->client->request('POST', '/tasks/1/delete');
        $this->client->followRedirect();
        $this->assertContains('Vous ne pouvez pas supprimer cette tâche. Seul un administrateur du site le peut.', $this->client->getResponse()->getContent());
    }

    public function testDeleteATaskOfAnOtherUserShouldReturnAnErrorMessage(){
        $this->login(['ROLE_USER']);

        $fakeUser = new User();
        $fakeUser->setEmail('johne.doe@monmail.com');
        $fakeUser->setUsername('JohneDoe');
        $fakeUser->setPassword('$2y$13$JupoD6T7C6wzIaXCQoZyK.GIWG9MO9jwgS9iMgYKy788aG7.Tyd92');
        $this->em->persist($fakeUser);

        $fakeTask = new Task();
        $fakeTask->setCreatedAt(new \DateTime());
        $fakeTask->setTitle('Le titre de mon test');
        $fakeTask->setContent('Le contenu de mon test');
        $fakeTask->setUser($fakeUser);
        $this->em->persist($fakeTask);

        $this->em->flush();

        $this->client->request('POST', '/tasks/1/delete');
        $this->client->followRedirect();
        $this->assertContains('Vous ne pouvez pas supprimer cette tâche. Seul son propriétaire le peut.', $this->client->getResponse()->getContent());
    }

    public function testListShouldReturnATask(){

        $this->login(['ROLE_USER']);
        $crawler = $this->client->request('GET', '/tasks/create');
        $form = $crawler->selectButton('Ajouter')->form();
        $form['task[title]'] = 'Le titre de mon test';
        $form['task[content]'] = 'Le contenu de mon test';

        $this->client->submit($form);

        $this->client->request('GET', '/tasks');

        $this->assertContains('Le titre de mon test', $this->client->getResponse()->getContent());

        $this->assertEquals($this->client->getResponse()->getStatusCode(), 200);

    }

    private function login($roles){
        $session = $this->container->get('session');

        // the firewall context defaults to the firewall name
        $firewallContext = 'main';

        $token = new UsernamePasswordToken($this->user, null, $firewallContext, $roles);
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
