<?php
/**
 * CollectFriendsCommand
 *
 * @package
 * @author    Cornelius Adams (conlabz GmbH) <ca@conlabz.de>
 */

namespace App\Console\Command;


use App\Collector\FriendsCollector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CollectFriendsCommand extends Command
{
    /**
     * @var FriendsCollector
     */
    private $friendsCollector;

    public function __construct(FriendsCollector $friendsCollector, string $name = 'twitter:collect:friends')
    {
        parent::__construct($name);
        $this->friendsCollector = $friendsCollector;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->friendsCollector->collect($output);
    }
}
