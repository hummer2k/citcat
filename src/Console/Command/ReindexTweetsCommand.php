<?php
/**
 * ReindexTweetsCommand
 *
 * @package
 * @author    Cornelius Adams (conlabz GmbH) <ca@conlabz.de>
 */

namespace App\Console\Command;


use App\Indexer\Elasticsearch;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReindexTweetsCommand extends Command
{
    /**
     * @var Elasticsearch
     */
    private $elasticsearch;

    public function __construct(Elasticsearch $elasticsearch, string $name = 'twitter:tweets:reindex')
    {
        parent::__construct($name);
        $this->elasticsearch = $elasticsearch;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->elasticsearch->reindex($output);
    }
}
