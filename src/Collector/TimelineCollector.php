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
use Symfony\Component\Console\Output\OutputInterface;

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
     * TimeLineCollector constructor.
     * @param TwitterOAuth $twitterOAuth
     * @param TweetRepository $tweetRepository
     * @param TimelineResponseHandler $timelineResponseHandler
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        TwitterOAuth $twitterOAuth,
        TweetRepository $tweetRepository,
        TimelineResponseHandler $timelineResponseHandler,
        EntityManagerInterface $entityManager
    ) {
        $this->twitterOAuth = $twitterOAuth;
        $this->tweetRepository = $tweetRepository;
        $this->timelineResponseHandler = $timelineResponseHandler;
        $this->entityManager = $entityManager;
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
        $keepAlive = new ConnectionKeepAlive();
        $keepAlive->addConnection($this->entityManager->getConnection());
        $keepAlive->attach();

        while (true) {
            $params = array_replace(
                [
                    'count' => 200,
                    'tweet_mode' => 'extended'
                ],
                $params
            );

            $output->writeln(sprintf('Fetching tweets from timeline ...'));
            $response = $this->twitterOAuth->get('statuses/home_timeline', $params);
            $this->timelineResponseHandler->handleResponse($response, $output);
        }

        $keepAlive->detach();
    }
}
