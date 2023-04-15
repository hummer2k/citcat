<?php

namespace App\Console\Command;


use App\Collector\DeletedTweetsCollector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CollectDeletedTweetsCommand extends Command
{
    /**
     * @var DeletedTweetsCollector
     */
    private $deletedTweetsCollector;

    public function __construct(
        DeletedTweetsCollector $deletedTweetsCollector,
        string $name = 'twitter:collect:deleted-tweets'
    ) {
        parent::__construct($name);
        $this->deletedTweetsCollector = $deletedTweetsCollector;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->deletedTweetsCollector->collect($output);
    }
}
