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
use App\Repository\TweetRepository;
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
     * TimeLineCollector constructor.
     * @param TwitterOAuth $twitterOAuth
     * @param TweetRepository $tweetRepository
     * @param TimelineResponseHandler $timelineResponseHandler
     */
    public function __construct(
        TwitterOAuth $twitterOAuth,
        TweetRepository $tweetRepository,
        TimelineResponseHandler $timelineResponseHandler
    ) {
        $this->twitterOAuth = $twitterOAuth;
        $this->tweetRepository = $tweetRepository;
        $this->timelineResponseHandler = $timelineResponseHandler;
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
    }
}
