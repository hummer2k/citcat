<?php
namespace App\Repository;

use App\Entity\Tweet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class TweetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, $entityClass = Tweet::class)
    {
        parent::__construct($registry, $entityClass);
    }

    /**
     * @param array $tweets
     * @return mixed|void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveBulk(array $tweets)
    {
        foreach ($tweets as $tweetData) {
            /** @var Tweet $tweet */
            $tweet = $this->find($tweetData->id) ?: new Tweet();
            $tweet->exchangeArray((array) $tweetData);
            $this->getEntityManager()->persist($tweet);
        }
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();
    }

    /**
     * @return string|null
     */
    public function getSinceId(): ?string
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select('MAX(t.id) AS since_id');
        $qb->setMaxResults(1);

        try {
            $sinceId = $qb->getQuery()->getSingleScalarResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }

        return $sinceId;
    }

    /**
     * @param callable|null $predicate
     * @return IterableResult|Tweet[][]
     */
    public function getTweetIterator(callable $predicate = null)
    {
        $qb = $this->createQueryBuilder('t');
        if (null !== $predicate) {
            $predicate($qb);
        }
        return $qb->getQuery()->iterate();
    }
}
