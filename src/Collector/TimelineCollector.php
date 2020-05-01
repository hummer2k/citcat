<?php
/**
 * TimeLineCollector
 *
 * @package
 * @author    Cornelius Adams (conlabz GmbH) <ca@conlabz.de>
 */

namespace App\Collector;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Collector\Handler\TimelineResponseHandler;
use App\Helper\ConnectionKeepAlive;
use App\Repository\TweetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Store\FlockStore;

class TimelineCollector implements CollectorInterface
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
     * @var TimelineResponseHandler
     */
    private $timelineResponseHandler;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * TimeLineCollector constructor.
     * @param TwitterOAuth $twitterOAuth
     * @param TweetRepository $tweetRepository
     * @param TimelineResponseHandler $timelineResponseHandler
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        TwitterOAuth $twitterOAuth,
        TweetRepository $tweetRepository,
        TimelineResponseHandler $timelineResponseHandler,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->twitterOAuth = $twitterOAuth;
        $this->tweetRepository = $tweetRepository;
        $this->timelineResponseHandler = $timelineResponseHandler;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * @param array $params
     * @return array
     */
    private function mergeParams(array $params = []): array
    {
        $defaultParams =  [
            'count' => 200,
            'tweet_mode' => 'extended',
            'memory_limit' => 2 * 1024 * 1024 * 1024
        ];
        foreach ($defaultParams as $key => $value) {
            if (!isset($params[$key])) {
                $params[$key] = $value;
            }
        }
        return $params;
    }

    /**
     * @param OutputInterface|null $output
     * @param array $params
     * @return mixed|void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function collect(OutputInterface $output = null, array $params = [])
    {
        $lockFactory = new Factory(new FlockStore());
        $lock = $lockFactory->createLock('timeline_collector');

        if (!$lock->acquire()) {
            return;
        }

        $params = $this->mergeParams($params);

        try {
            $keepAlive = new ConnectionKeepAlive();
            $keepAlive->addConnection($this->entityManager->getConnection());
            $keepAlive->attach();

            while ($params['memory_limit'] >= memory_get_usage(true)) {
                $output->writeln(sprintf('Fetching tweets from timeline ...'));
                $response = $this->twitterOAuth->get('statuses/home_timeline', [
                    'count' => 200,
                    'tweet_mode' => 'extended'
                ]);
                $this->timelineResponseHandler->handleResponse($response, $output);
            }
        } finally {
            $keepAlive->detach();
            $lock->release();
        }
    }
}
