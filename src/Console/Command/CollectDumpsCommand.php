<?php
/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace App\Console\Command;

use App\Api\Twitter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CollectDumpsCommand extends Command
{
    /**
     * @var Twitter
     */
    private $twitter;

    public function __construct(Twitter $twitter, string $name = 'twitter:collect:dumps')
    {
        parent::__construct($name);
        $this->twitter = $twitter;
    }

    protected function configure()
    {
        parent::configure();
        $this->addOption('path', null, InputOption::VALUE_OPTIONAL, 'File glob path');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getOption('path') ?: 'var/dumps/*.json';
        $files = glob($path);
        $progressBar = new ProgressBar($output);
        $progressBar->start(count($files));
        foreach ($files as $file) {
            $response = json_decode(file_get_contents($file));
            $tweetIds = $this->twitter->idify($response->results);
            $this->twitter->fetchTweetsByIds($tweetIds);
            $progressBar->advance();
        }
        $progressBar->finish();
    }
}
