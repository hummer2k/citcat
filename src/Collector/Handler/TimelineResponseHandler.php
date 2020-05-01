<?php
/**
 * SearchResponseHandler
 *
 * @package
 * @author    Cornelius Adams (conlabz GmbH) <ca@conlabz.de>
 */

namespace App\Collector\Handler;

use App\Helper\CollectHelper;
use App\Repository\TweetRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use stdClass;
use Symfony\Component\Console\Output\OutputInterface;

class TimelineResponseHandler
{
    /**
     * @var TweetRepository
     */
    private $tweetRepository;

    /**
     * @var int
     */
    private $requestInterval;

    /**
     * @var int
     */
    private $defaultRequestInterval = 60;

    /**
     * @var int
     */
    private $maxRequestInterval = 600;

    /**
     * @var CollectHelper
     */
    private $collectHelper;

    /**
     * SearchResponseHandler constructor.
     * @param TweetRepository $tweetRepository
     * @param CollectHelper $collectHelper
     */
    public function __construct(TweetRepository $tweetRepository, CollectHelper $collectHelper)
    {
        $this->tweetRepository = $tweetRepository;
        $this->requestInterval = $this->defaultRequestInterval;
        $this->collectHelper = $collectHelper;
    }

    /**
     * @param array|stdClass $response
     * @param OutputInterface $output
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function handleResponse($response, OutputInterface $output)
    {
        if (isset($response->errors)) {
            $this->calculateNextRequestInterval(2);
            foreach ($response->errors as $error) {
                $output->writeln(sprintf('<error>%s (Code: %d)</error>', $error->message, $error->code));
            }
        } elseif (empty($response)) {
            $this->calculateNextRequestInterval(1.5);
            $output->writeln('<comment>No tweets found</comment>');
        } else {
            $currentTweetCount = $this->tweetRepository->count([]);
            $this->tweetRepository->saveBulk($response);
            $insertedTweetCount = $this->tweetRepository->count([]) - $currentTweetCount;

            if ($insertedTweetCount > 0) {
                $output->writeln(sprintf('<info>Found %d new tweets</info>', $insertedTweetCount));
                $this->resetRequestInterval();
            } else {
                $output->writeln('<comment>No tweets found</comment>');
                $this->calculateNextRequestInterval(1.5);
            }
        }

        for ($i = $this->requestInterval; $i > 0; $i--) {
            $output->write(
                sprintf(
                    'Try again in %d seconds, Memory usage: %s',
                    $i,
                    $this->collectHelper->formatBytes(memory_get_usage(true))
                ) . "\t\t\r"
            );
            sleep(1);
        }
    }

    /**
     * @param float $multiplier
     */
    private function calculateNextRequestInterval(float $multiplier): void
    {
        $this->requestInterval = min($this->maxRequestInterval, round($this->requestInterval * $multiplier));
    }

    /**
     * @return void
     */
    private function resetRequestInterval(): void
    {
        $this->requestInterval = $this->defaultRequestInterval;
    }
}
