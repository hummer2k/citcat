<?php
/**
 * TwitterTestCommand
 *
 * @package
 * @author    Cornelius Adams (conlabz GmbH) <ca@conlabz.de>
 */

namespace App\Console\Command;


use Abraham\TwitterOAuth\TwitterOAuth;
use App\Entity\Tweet;
use App\Repository\TweetRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TwitterTestCommand extends Command
{

    /**
     * @var TwitterOAuth
     */
    private $twitterApi;

    /**
     * @var TweetRepository
     */
    private $tweetRepository;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function __construct(
        TwitterOAuth $twitterApi,
        TweetRepository $tweetRepository,
        ObjectManager $objectManager,
        string $name = 'twitter:test'
    ) {
        parent::__construct($name);
        $this->twitterApi = $twitterApi;
        $this->tweetRepository = $tweetRepository;
        $this->objectManager = $objectManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $iterator = $this->tweetRepository->getTweetIterator();
        $i = 0;
        foreach ($iterator as $row) {
            /** @var Tweet $tweet */
            $tweet = $row[0];
            $rawData = $tweet->getRawData();
            $tweet->setScreenName($rawData['user']['screen_name']);
            if ($i++ >= 100) {
                $this->objectManager->flush();
                $this->objectManager->clear();
            }
        }

        $this->objectManager->flush();

        /*$tweets = $this->twitterApi->get('statuses/show', [
            'id' => '1148237087507922945'
        ]);

        $output->write(print_r($tweets, true));*/
    }
}
