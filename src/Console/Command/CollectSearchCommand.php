<?php

namespace App\Console\Command;

use App\Collector\SearchCollector;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CollectSearchCommand extends Command
{
    /**
     * @var SearchCollector
     */
    private $searchCollector;

    /**
     * LastMonthCollectorCommand constructor.
     * @param SearchCollector $searchCollector
     * @param string|null $name
     */
    public function __construct(SearchCollector $searchCollector, string $name = 'twitter:collect:search')
    {
        parent::__construct($name);
        $this->searchCollector = $searchCollector;
    }

    protected function configure()
    {
        parent::configure();
        $this->addOption(
            'result_type',
            null,
            InputOption::VALUE_OPTIONAL,
            "Optional. Specifies what type of search results you would prefer to receive. " .
            "The current default is 'mixed.' Valid values include:\n" .
            "* mixed : Include both popular and real time results in the response.\n" .
            "* recent : return only the most recent results in the response\n" .
            "* popular : return only the most popular results in the response.",
            'mixed'
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->searchCollector->collect($output, [
            'result_type' => $input->getOption('result_type')
        ]);
    }
}
