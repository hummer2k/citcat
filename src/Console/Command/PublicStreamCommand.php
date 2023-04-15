<?php

namespace App\Console\Command;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Api\Twitter;
use App\Helper\ConnectionKeepAlive;
use Doctrine\ORM\EntityManagerInterface;
use Spatie\TwitterStreamingApi\PublicStream;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Store\FlockStore;

class PublicStreamCommand extends Command
{
    /**
     * @var PublicStream
     */
    private $publicStream;

    /**
     * @var TwitterOAuth
     */
    private $twitterApi;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Twitter
     */
    private $twitter;

    /**
     * PublicStreamCommand constructor.
     * @param PublicStream $publicStream
     * @param TwitterOAuth $twitterApi
     * @param EntityManagerInterface $entityManager
     * @param string|null $name
     */
    public function __construct(
        PublicStream $publicStream,
        TwitterOAuth $twitterApi,
        Twitter $twitter,
        EntityManagerInterface $entityManager,
        string $name = 'twitter:public-stream'
    ) {
        parent::__construct($name);
        $this->publicStream = $publicStream;
        $this->twitterApi = $twitterApi;
        $this->entityManager = $entityManager;
        $this->twitter = $twitter;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lockFactory = new Factory(new FlockStore());
        $lock = $lockFactory->createLock('public-stream');

        if (!$lock->acquire()) {
            $output->writeln('<info>Locked</info>');
            return 0;
        }

        $keepAlive = new ConnectionKeepAlive();
        $keepAlive->addConnection($this->entityManager->getConnection());
        $keepAlive->attach();

        try {
            $twitterUseIds = $this->twitterApi->get('friends/ids')->ids;
            $this->publicStream->whenTweets(
                $twitterUseIds,
                function ($tweet) use ($output, $twitterUseIds) {
                    if (!isset($tweet['user'])) {
                        $output->writeln('<comment>' . sprintf('%s is not a tweet', $tweet['id_str']) . '</comment>');
                        return;
                    }
                    if (in_array($tweet['user']['id'], $twitterUseIds)) {
                        $output->writeln(print_r($tweet, true));
                        $this->twitter->fetchTweetsByIds([$tweet['id']]);
                        $output->writeln(sprintf('<info>Fetched tweet: %s</info>', $tweet['id_str']));
                    }
                }
            )->startListening();
        } catch (\Throwable $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return 1;
        } finally {
            $keepAlive->detach();
            $lock->release();
        }
        return 0;
    }
}
