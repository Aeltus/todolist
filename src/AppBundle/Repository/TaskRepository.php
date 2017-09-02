<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 02/09/17
 * Time: 15:51
 */
namespace AppBundle\Repository;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class TaskRepository extends EntityRepository
{
    public function findAllForUser(User $user)
    {
        $qb = $this
            ->createQueryBuilder('t')
            ->leftJoin('t.user', 'u')
            ->orderBy('t.createdAt', 'ASC')
            ->where('u.username = ?1')
            ->setParameter(1, $user->getUsername())
        ;

        return $qb->getQuery()->getResult();
    }
}