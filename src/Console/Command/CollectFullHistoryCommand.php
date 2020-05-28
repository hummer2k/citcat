<?php
/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace App\Console\Command;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Api\Twitter;
use App\Helper\CollectHelper;
use App\Repository\TweetRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CollectFullHistoryCommand extends Command
{
    /**
     * @var Twitter
     */
    private $twitter;

    /**
     * @var TwitterOAuth
     */
    private $twitterOAuth;

    /**
     * @var CollectHelper
     */
    private $collectHelper;

    /**
     * @var TweetRepository
     */
    private $tweetRepository;

    /**
     * CollectFullHistoryCommand constructor.
     * @param Twitter $twitter
     * @param TwitterOAuth $twitterOAuth
     * @param CollectHelper $collectHelper
     * @param TweetRepository $tweetRepository
     * @param string $name
     */
    public function __construct(
        Twitter $twitter,
        TwitterOAuth $twitterOAuth,
        CollectHelper $collectHelper,
        TweetRepository $tweetRepository,
        string $name = 'twitter:collect:full-history'
    ) {
        parent::__construct($name);
        $this->twitter = $twitter;
        $this->twitterOAuth = $twitterOAuth;
        $this->collectHelper = $collectHelper;
        $this->tweetRepository = $tweetRepository;
    }

    protected function configure()
    {
        $this->addOption('from-date', null, InputOption::VALUE_OPTIONAL, 'Start date');
        $this->addOption('to-date',   null, InputOption::VALUE_OPTIONAL, 'End date');
        $this->addOption('type',      null, InputOption::VALUE_OPTIONAL, 'Type: 30day or fullarchive', '30day');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $friends = $this->twitter->getFriends();
        $queries = $this->collectHelper->generateFromQueries($friends->users, 256);

        $type = $input->getOption('type');

        $params = [
            'maxResults' => 100
        ];

        if ($fromDate = $input->getOption('from-date')) {
            $params['fromDate'] = (new \DateTime($fromDate))->format('YmdHi');
        }
        if ($toDate = $input->getOption('to-date')) {
            $params['toDate'] = (new \DateTime($toDate))->format('YmdHi');
        }

        $progressBar = new ProgressBar($output, count($queries));
        $progressBar->display();
        $current = 1;

        foreach ($queries as $query) {
            $mergedParams = array_merge(
                [
                    'query' => $query,
                ],
                $params
            );

            $page = 1;
            do {

                $cacheFile = __DIR__ . '/../../../var/dumps/' . md5(json_encode($mergedParams)) . '.json';
                $progressBar->setMessage(sprintf('Page: %d', $page));

                if (!file_exists($cacheFile)) {
                    $response =  $this->twitterOAuth->get(sprintf('tweets/search/%s/dev', $type), $mergedParams);
                    file_put_contents(
                        $cacheFile,
                        json_encode($response)
                    );
                } else {
                    $response = json_decode(file_get_contents($cacheFile));
                }

                if (isset($response->errors)) {
                    $this->collectHelper->outputErrors($response->errors, $output);
                    $this->collectHelper->wait(300, $output);
                }

                $tweetIds = $this->twitter->idify($response->results);
                $this->twitter->fetchTweetsByIds($tweetIds);

                if (isset($response->next)) {
                    $updatedParams = [
                        'next' => $response->next
                    ];
                    $mergedParams = array_replace($mergedParams, $updatedParams);
                    $page++;
                }
            } while (isset($response->next));
            $current++;

            $progressBar->advance();
        }

        $progressBar->finish();
    }
}
