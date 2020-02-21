<?php
/**
 * PublicStreamCommand
 *
 * @package
 * @author    Cornelius Adams (conlabz GmbH) <ca@conlabz.de>
 */

namespace App\Console\Command;


use Abraham\TwitterOAuth\TwitterOAuth;
use Spatie\TwitterStreamingApi\PublicStream;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
     * PublicStreamCommand constructor.
     * @param PublicStream $publicStream
     * @param TwitterOAuth $twitterApi
     * @param string|null $name
     */
    public function __construct(PublicStream $publicStream, TwitterOAuth $twitterApi, string $name = 'twitter:public-stream')
    {
        parent::__construct($name);
        $this->publicStream = $publicStream;
        $this->twitterApi = $twitterApi;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $twitterUseIds = $this->twitterApi->get('friends/ids')->ids;
        $this->publicStream->whenTweets(
            $twitterUseIds,
            function (array $tweet) use ($output) {
                $output->writeln(print_r($tweet, true));
            }
        )->startListening();
    }
}
