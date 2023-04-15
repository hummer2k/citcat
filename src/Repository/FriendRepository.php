<?php

namespace App\Repository;

use App\Entity\Friend;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class FriendRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, $entityClass = Friend::class)
    {
        parent::__construct($registry, $entityClass);
    }

    /**
     * @param array $friends
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveBulk(array $friends)
    {
        foreach ($friends as $friendData) {
            $friendData = (array) $friendData;

            $friend = new Friend();
            $friend->setId($friendData['id']);
            $friend->setName($friendData['name']);
            $friend->setScreenName($friendData['screen_name']);
            $friend->setDescription($friendData['description']);
            $friend->setUrl($friendData['url']);
            $friend->setRawData($friendData);

            $this->getEntityManager()->merge($friend);
        }
        $this->getEntityManager()->flush();
    }
}
