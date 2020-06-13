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
use Doctrine\Common\Collections\Criteria;
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
        $this->addOption('from-users', null, InputOption::VALUE_OPTIONAL, 'comma separated list of user names');
        $this->addOption('dataset', null, InputOption::VALUE_REQUIRED, 'Path to dataset');
        $this->addOption('category', null, InputOption::VALUE_REQUIRED, 'Category');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $categoryId = $input->getOption('category');
        /** @var Category $category */
        $category = $this->categoryRepository->find($categoryId);
        if (!$category && !is_numeric($categoryId)) {
            $category = new Category();
            $category->setName($categoryId);
            $this->objectManager->persist($category);
        } elseif (!$category) {
            $output->writeln(sprintf('<error>Category "%s" not found.</error>', $categoryId));
            return 1;
        }

        if ($dataset = $input->getOption('dataset')) {
            $file = $this->kernel->getProjectDir() . '/data/' . $dataset;
            if (!is_file($file) || !is_readable($file)) {
                $output->writeln('<error>' . $file . ' is not readable.</error>');
                return 1;
            }
            $tweetIds = array_unique(array_map('trim', file($file)));
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
        } elseif ($users = $input->getOption('from-users')) {
            $users = array_unique(array_map('trim', explode(',', $users)));
            $criteria = Criteria::create();
            $criteria->where($criteria::expr()->in('screenName', $users));
            /** @var Tweet[] $tweets */
            $tweets = $this->tweetRepository->matching($criteria);
            foreach ($tweets as $tweet) {
                $tweet->addCategory($category);
            }
        }

        $this->objectManager->flush();
    }
}
