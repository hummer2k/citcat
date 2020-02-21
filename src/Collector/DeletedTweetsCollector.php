<?php
/**
 * DeletedTweetsCollector
 *
 * @package
 * @author    Cornelius Adams (conlabz GmbH) <ca@conlabz.de>
 */

namespace App\Collector;


use Abraham\TwitterOAuth\TwitterOAuth;
use App\Entity\Tweet;
use App\Repository\TweetRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class DeletedTweetsCollector implements CollectorInterface
{
    /**
     * @var TwitterOAuth
     */
    private $twitterOAuth;

    /**
     * @var TweetRepository
     */
    private $tweetRepository;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var int
     */
    private $deletedTweets = 0;

    /**
     * DeletedTweetsCollector constructor.
     * @param TwitterOAuth $twitterOAuth
     * @param TweetRepository $tweetRepository
     * @param ObjectManager $objectManager
     */
    public function __construct(
        TwitterOAuth $twitterOAuth,
        TweetRepository $tweetRepository,
        ObjectManager $objectManager
    ) {
        $this->twitterOAuth = $twitterOAuth;
        $this->tweetRepository = $tweetRepository;
        $this->objectManager = $objectManager;
    }

    /**
     * @param OutputInterface|null $output
     * @param array|null $params
     * @return mixed
     */
    public function collect(OutputInterface $output = null, array $params = [])
    {
        $iterator = $this->tweetRepository->getTweetIterator(function (QueryBuilder $qb) {
            $qb->where('t.isDeleted = 0');
            $qb->orderBy('t.id', 'DESC');
        });
        $count = $this->tweetRepository->count(['isDeleted' => 0]);
        $batchSize = 100;
        $tweets = [];

        $progress = new ProgressBar($output);
        $progress->start($count);

        foreach ($iterator as $row) {
            /** @var Tweet $tweet */
            $tweet = $row[0];
            $tweets[$tweet->getId()] = $tweet;

            if (count($tweets) >= $batchSize) {
                $this->processBatch($tweets);
                $tweets = [];
                $progress->advance($batchSize);
            }
        }

        $this->processBatch($tweets);
        $progress->finish();
        $output->writeln('');
        $output->writeln(sprintf('<comment>%d tweets have been deleted</comment>', $this->deletedTweets));
    }

    /**
     * @param array $tweets |Tweet[]
     */
    private function processBatch(array $tweets)
    {
        $result = $this->twitterOAuth->get('statuses/lookup', [
            'id' => implode(',', array_keys($tweets)),
            'map' => true,
        ]);

        foreach ($result->id as $tweetId => $tweetData) {
            if (!$tweetData) {
                /** @var Tweet $tweet */
                $tweet = $tweets[$tweetId];
                $tweet->setIsDeleted(true);
                $this->deletedTweets++;
            }
        }

        $this->objectManager->flush();
        $this->objectManager->clear();
    }
}
