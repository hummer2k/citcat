<?php
/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace App\Console\Command;

use App\Entity\Category;
use App\Entity\Tweet;
use App\Kernel;
use App\Repository\CategoryRepository;
use App\Repository\TweetRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class ImportDatasetCommand extends Command
{
    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var TweetRepository
     */
    private $tweetRepository;

    /**
     * @var ObjectManager|EntityManagerInterface
     */
    private $objectManager;

    public function __construct(
        KernelInterface $kernel,
        CategoryRepository $categoryRepository,
        TweetRepository $tweetRepository,
        ObjectManager $objectManager,
        string $name = 'twitter:import:dataset'
    ) {
        parent::__construct($name);
        $this->kernel = $kernel;
        $this->categoryRepository = $categoryRepository;
        $this->tweetRepository = $tweetRepository;
        $this->objectManager = $objectManager;
    }

    protected function configure()
    {
        parent::configure();
        $this->addOption('dataset', null, InputOption::VALUE_REQUIRED, 'Path to dataset');
        $this->addOption('category-id', null, InputOption::VALUE_REQUIRED, 'Category Id');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $categoryId = $input->getOption('category-id');
        if (!$categoryId) {
            $output->writeln('Please specify the category-id');
            return 1;
        }
        $dataset = $input->getOption('dataset');
        $file = $this->kernel->getProjectDir() . '/data/' . $dataset;
        if (!is_file($file) || !is_readable($file)) {
            $output->writeln('<error>' . $file . ' is not readable.</error>');
            return 1;
        }
        $tweetIds = array_unique(array_map('trim', file($file)));

        /** @var Category $category */
        $category = $this->categoryRepository->find($categoryId);

        foreach ($tweetIds as $tweetId) {
            $tweetId = trim($tweetId);
            if (!$tweetId) continue;
            /** @var Tweet $tweet */
            $tweet = $this->tweetRepository->find($tweetId);
            if (!$tweet) {
                $output->writeln('<comment>' . $tweetId . ' not found in database.</comment>');
                continue;
            }
            $tweet->addCategory($category);
        }
        $this->objectManager->flush();
    }
}
