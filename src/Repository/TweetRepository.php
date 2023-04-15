<?php

namespace App\Repository;

use Adbar\Dot;
use App\Entity\Category;
use App\Entity\Tweet;
use App\Helper\CollectHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class TweetRepository extends ServiceEntityRepository
{
    /**
     * @var CollectHelper
     */
    private $collectHelper;

    public function __construct(ManagerRegistry $registry, CollectHelper $collectHelper, $entityClass = Tweet::class)
    {
        parent::__construct($registry, $entityClass);
        $this->collectHelper = $collectHelper;
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
            $tweetData = $this->collectHelper->normalizeTweetData($tweetData);
            /** @var Tweet $tweet */
            $tweet = $this->find($tweetData['id']) ?: new Tweet();
            $tweet->exchangeArray($tweetData);
            $this->getEntityManager()->persist($tweet);
        }
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();
    }

    /**
     * @param array $tweets
     * @param array $fields
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     */
    public function updateBulk(array $tweets, array $fields = [])
    {
        foreach ($tweets as $tweetData) {
            /** @var Tweet $tweet */
            $tweet = $this->find($tweetData['id']);
            if (!$tweet) {
                continue;
            }
            $tweetData = new Dot($tweetData);
            $rawData = new Dot($tweet->getRawData());

            foreach ($fields as $field) {
                if ($rawData->has($field) && $tweetData->has($field)) {
                    $rawData->set($field, $tweetData->get($field));
                }
            }
            $tweet->setRawData($rawData->all());
            $tweet->updatedTimestamps();
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

    public function findByCategory(Category $category)
    {
        $qb = $this->createQueryBuilder('t');
        $qb->where(':category MEMBER OF t.categories')
            ->setParameter('category', $category);
        return $qb->getQuery()->getResult();
    }
}
