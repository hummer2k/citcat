<?php

namespace App\Collector;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Helper\CollectHelper;
use App\Repository\TweetRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SearchCollector implements CollectorInterface
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
     * @var CollectHelper
     */
    private $helper;

    /**
     * LastMonthCollector constructor.
     * @param TwitterOAuth $twitterOAuth
     * @param TweetRepository $tweetRepository
     * @param CollectHelper $helper
     */
    public function __construct(
        TwitterOAuth $twitterOAuth,
        TweetRepository $tweetRepository,
        CollectHelper $helper
    ) {
        $this->twitterOAuth = $twitterOAuth;
        $this->tweetRepository = $tweetRepository;
        $this->helper = $helper;
    }

    /**
     * @param OutputInterface|null $output
     * @param array $params
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function collect(OutputInterface $output = null, array $params = [])
    {
        $friends = $this->getFriends();
        $queries = $this->helper->generateFromQueries($friends->users);

        $oldTweetCount = $this->tweetRepository->count([]);
        $output->writeln(sprintf('Start collecting tweets of %d followers', count($friends->users)));
        $current = 1;

        $progressBar = new ProgressBar($output, count($queries));
        $progressBar->display();

        foreach ($queries as $query) {

            $mergedParams = array_merge([
                'q' => $query,
                'count' => 100,
                'tweet_mode' => 'extended',
                'result_type' => 'recent',
                'include_entities' => true
            ], $params);

            $page = 1;
            do {

                $progressBar->setMessage(sprintf('Page: %d', $page));
                $response = $this->twitterOAuth->get('search/tweets', $mergedParams);

                if (isset($response->errors)) {
                    $this->helper->outputErrors($response->errors, $output);
                    $this->helper->wait(300, $output);
                    $this->collect($output, $params);
                }

                $this->tweetRepository->saveBulk($response->statuses);

                if (isset($response->search_metadata->next_results)) {
                    $updatedParams = [];
                    parse_str(rawurldecode(substr($response->search_metadata->next_results, 1)), $updatedParams);
                    $mergedParams = array_replace($mergedParams, $updatedParams);
                    $page++;
                }

            } while (isset($response->search_metadata->next_results));
            $current++;

            $progressBar->advance();
        }

        $progressBar->finish();

        $insertedTweetCount = $this->tweetRepository->count([]) - $oldTweetCount;

        if ($insertedTweetCount > 0) {
            $output->writeln(sprintf('<info>Fetched %d new tweets</info>', $insertedTweetCount));
        } else {
            $output->writeln('<comment>No tweets found</comment>');
        }
    }

    private function getFriends()
    {
        return $this->twitterOAuth->get('friends/list', [
            'count' => 200
        ]);
    }
}
