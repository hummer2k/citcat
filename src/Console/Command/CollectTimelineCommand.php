<?php
/**
 * CollectTimeLineHistoryCommand
 *
 * @package
 * @author    Cornelius Adams (conlabz GmbH) <ca@conlabz.de>
 */

namespace App\Console\Command;


use Abraham\TwitterOAuth\TwitterOAuth;
use App\Collector\TimelineCollector;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CollectTimelineCommand extends Command
{
    /**
     * @var TwitterOAuth
     */
    private $timelineCollector;

    public function __construct(
        TimelineCollector $timelineCollector,
        string $name = 'twitter:collect:timeline'
    ) {
        parent::__construct($name);
        $this->timelineCollector = $timelineCollector;
    }

    protected function configure()
    {
        parent::configure();
        $this->addOption(
            'max_id',
            null,
            InputOption::VALUE_OPTIONAL,
            'Returns results with an ID less than (that is, older than) or equal to the specified ID.'
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
        $params = [];
        if ($maxId = $input->getOption('max_id')) {
            $params['max_id'] = $maxId;
        }
        $this->timelineCollector->collect($output, $params);
    }
}
