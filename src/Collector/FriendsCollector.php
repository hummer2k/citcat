<?php

namespace App\Collector;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Repository\FriendRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Console\Output\OutputInterface;

class FriendsCollector implements CollectorInterface
{
    /**
     * @var TwitterOAuth
     */
    private $twitterOAuth;

    /**
     * @var FriendRepository
     */
    private $friendRepository;

    /**
     * FriendsCollector constructor.
     * @param TwitterOAuth $twitterOAuth
     * @param FriendRepository $friendRepository
     */
    public function __construct(TwitterOAuth $twitterOAuth, FriendRepository $friendRepository)
    {
        $this->twitterOAuth = $twitterOAuth;
        $this->friendRepository = $friendRepository;
    }

    /**
     * @param OutputInterface|null $output
     * @param array $params
     * @return mixed|void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function collect(OutputInterface $output = null, array $params = [])
    {
        $friendsList = $this->twitterOAuth->get('friends/list', [
            'count' => 200
        ]);
        $this->friendRepository->saveBulk($friendsList->users);
    }
}
