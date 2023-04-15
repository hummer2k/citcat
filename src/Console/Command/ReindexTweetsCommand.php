<?php

namespace App\Console\Command;

use App\Indexer\Elasticsearch;
use App\Repository\TweetRepository;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReindexTweetsCommand extends Command
{
    public const OPTION_FROM_DATE = 'from-date';

    /**
     * @var Elasticsearch
     */
    private $elasticsearch;

    /**
     * @var TweetRepository
     */
    private $tweetRepository;

    public function __construct(
        Elasticsearch $elasticsearch,
        TweetRepository $tweetRepository,
        string $name = 'twitter:tweets:reindex'
    ) {
        parent::__construct($name);
        $this->elasticsearch = $elasticsearch;
        $this->tweetRepository = $tweetRepository;
    }

    protected function configure()
    {
        parent::configure();
        $this->addOption(
            self::OPTION_FROM_DATE,
            null,
            InputOption::VALUE_OPTIONAL,
            'From date'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tweets = null;
        if ($fromDate = $input->getOption(self::OPTION_FROM_DATE)) {
            $fromDate = new \DateTime($fromDate);
            $criteria = Criteria::create();
            $criteria->where(Criteria::expr()->gte('createdAt', $fromDate));
            $tweets = $this->tweetRepository->matching($criteria);
        }
        $this->elasticsearch->reindex($output, $tweets);
    }
}
