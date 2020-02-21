<?php

namespace App\Collector;

use Symfony\Component\Console\Output\OutputInterface;

interface CollectorInterface
{
    /**
     * @param OutputInterface|null $output
     * @param array|null $params
     * @return mixed
     */
    public function collect(OutputInterface $output = null, array $params = []);
}
