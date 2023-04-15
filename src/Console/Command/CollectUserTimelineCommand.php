<?php

namespace App\Console\Command;

use App\Api\Twitter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class CollectUserTimelineCommand extends Command
{
    /**
     * @var Twitter
     */
    private $twitter;

    /**
     * CollectUserTimelineCommand constructor.
     * @param Twitter $twitter
     * @param string $name
     */
    public function __construct(Twitter $twitter, string $name = 'twitter:collect:user-timeline')
    {
        parent::__construct($name);
        $this->twitter = $twitter;
    }

    protected function configure()
    {
        $this->addOption('user-id', null, InputOption::VALUE_OPTIONAL, 'User ID');
        $this->addOption('start-from', null, InputOption::VALUE_OPTIONAL, 'Start from x');
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface|ConsoleOutput $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userId = (int) $input->getOption('user-id');

        if ($userId) {
            $this->twitter->fetchTweetsByUserId($userId, new ProgressBar($output));
        } else {
            $startFrom = (int) $input->getOption('start-from');
            $friends = $this->twitter->getFriends();

            $section1 = $output->section();
            $section2 = $output->section();

            $globalProgress = new ProgressBar($section1, count($friends->users));
            $globalProgress->start();

            $fetchProgress = new ProgressBar($section2);

            foreach ($friends->users as $i => $friend) {
                if (!$startFrom || $i + 1 >= $startFrom) {
                    $fetchProgress->start();
                    $this->twitter->fetchTweetsByUserId($friend->id, $fetchProgress);
                    $fetchProgress->finish();
                }
                $globalProgress->advance();
            }
            $globalProgress->finish();
        }
    }
}
