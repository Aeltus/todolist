<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 04/09/17
 * Time: 21:20
 */
namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Task;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\User;

class LoadData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $userAdmin = new User();
        $userAdmin->setUsername('admin');
        $userAdmin->setPassword('$2y$13$f3hwtRCcp4M6xr92iGzV8eLTvoDWufTTza5.kw0E5SXrRe24VnHju');
        $userAdmin->setEmail('admin@monmail.com');
        $userAdmin->setRoles(['ROLE_ADMIN']);

        $manager->persist($userAdmin);

        $user = new User();
        $user->setUsername('user');
        $user->setPassword('$2y$13$f3hwtRCcp4M6xr92iGzV8eLTvoDWufTTza5.kw0E5SXrRe24VnHju');
        $user->setEmail('user@monmail.com');
        $user->setRoles(['ROLE_USER']);

        $manager->persist($user);

        $task = new Task();
        $task->setTitle('Titre de la tache');
        $task->setContent('Contenu de la tache.');
        $task->setUser($userAdmin);

        $manager->persist($task);

        $manager->flush();
    }
}
