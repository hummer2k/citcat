<?php

namespace App\Api;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Repository\TweetRepository;
use Symfony\Component\Console\Helper\ProgressBar;

class Twitter
{
    public const LOOKUP_MAX = 100;

    /**
     * @var TwitterOAuth
     */
    private $twitterOAuth;

    /**
     * @var TweetRepository
     */
    private $tweetRepository;

    public function __construct(TwitterOAuth $twitterOAuth, TweetRepository $tweetRepository)
    {
        $this->twitterOAuth = $twitterOAuth;
        $this->tweetRepository = $tweetRepository;
    }

    public function getFriends()
    {
        return $this->twitterOAuth->get(
            'friends/list',
            [
                'count' => 200
            ]
        );
    }

    public function lookup(array $ids, array $parameters = [])
    {
        $chunks = array_chunk($ids, self::LOOKUP_MAX);

        $tweets = [];
        foreach ($chunks as $ids) {
            $result = $this->twitterOAuth->get(
                'statuses/lookup',
                array_replace(
                    [
                        'id' => implode(',', $ids),
                        'include_entities' => true,
                        'tweet_mode' => 'extended',
                        'map' => true,
                    ],
                    $parameters
                )
            );
            foreach ($result as $tweet) {
                $tweets[] = $tweet;
            }
        }

        return $tweets;
    }

    public function fetchTweetsByIds(array $ids)
    {
        $tweets = $this->lookup($ids, ['map' => false]);
        $this->tweetRepository->saveBulk($tweets);
    }

    public function fetchTweetsByUserId(int $userId, ProgressBar $progressBar, $count = 200, $maxId = null)
    {
        $params = [
            'user_id' => $userId,
            'count' => $count,
            'tweet_mode' => 'extended',
            'include_rts' => true
        ];
        if ($maxId) {
            $params['max_id'] = $maxId;
        }

        $tweets = $this->twitterOAuth->get('statuses/user_timeline', $params);
        $this->tweetRepository->saveBulk($tweets);

        $progressBar->advance(count($tweets));

        if (count($tweets) > 0) {
            $maxId = end($tweets)->id - 1;
            $this->fetchTweetsByUserId($userId, $progressBar, $count, $maxId);
        }
    }

    /**
     * @param array $tweets
     * @return array
     */
    public function idify(array $tweets): array
    {
        $tweetIds = [];
        foreach ($tweets as $tweet) {
            $tweetIds[] = $tweet->id;
        }
        return $tweetIds;
    }
}
