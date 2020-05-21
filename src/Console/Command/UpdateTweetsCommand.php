<?php
/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace App\Console\Command;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Entity\Category;
use App\Entity\Tweet;
use App\Repository\CategoryRepository;
use App\Repository\TweetRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateTweetsCommand extends Command
{
    /**
     * @var TweetRepository
     */
    private $tweetRepository;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var TwitterOAuth
     */
    private $twitterOAuth;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function __construct(
        TweetRepository $tweetRepository,
        CategoryRepository $categoryRepository,
        TwitterOAuth $twitterOAuth,
        ObjectManager $objectManager,
        string $name = 'twitter:tweets:update'
    ) {
        parent::__construct($name);
        $this->tweetRepository = $tweetRepository;
        $this->categoryRepository = $categoryRepository;
        $this->twitterOAuth = $twitterOAuth;
        $this->objectManager = $objectManager;
    }

    protected function configure()
    {
        parent::configure();
        $this->addOption('category-id', null, InputOption::VALUE_OPTIONAL, 'Category ID');
        $this->addOption('tweet-id', null, InputOption::VALUE_OPTIONAL, 'Tweet ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $category = null;
        if ($categoryId = $input->getOption('category-id')) {
            /** @var Category $category */
            $category = $this->categoryRepository->find($categoryId);
            $rows = $this->tweetRepository->findByCategory($category);
        }

        if ($tweetId = $input->getOption('tweet-id')) {
            $rows = $this->tweetRepository->findBy(['id' => $tweetId]);
        }

        $count = count($rows);
        $batchSize = 100;
        $tweets = [];

        $progress = new ProgressBar($output);
        $progress->start($count);

        /** @var Tweet $tweet */
        foreach ($rows as $tweet) {
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
    }

    /**
     * @param array $tweets |Tweet[]
     */
    private function processBatch(array $tweets)
    {
        $result = $this->twitterOAuth->get('statuses/lookup', [
            'id' => implode(',', array_keys($tweets)),
            'include_entities' => true,
            'map' => true,
        ]);

        $tweets = [];
        foreach ($result->id as $tweetId => $tweetData) {
            if ($tweetData) {
                $tweets[] = \json_decode(\json_encode($tweetData), true);
            }
        }

        $this->tweetRepository->updateBulk($tweets, [
            'retweet_count',
            'favorite_count',
            'retweeted_status.retweet_count',
            'retweeted_status.favorite_count'
        ]);
    }
}
