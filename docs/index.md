### Retrieve all followees

https://developer.twitter.com/en/docs/twitter-api/v1/accounts-and-users/follow-search-get-users/api-reference/get-friends-list

**Endpoint:** `GET friends/list`
Response:
```json
{
    "users": [
        {
            "id": "...",
            "screen_name": "Beatrix_vStorch"
        },
        {
            "id": "...",
            "screen_name": "Alice_Weidel"
        },
        {
            "id": "...",
            "screen_name": "Tino_Chrupalla"
        }
    ]
}
```

**Code:** `src/Collector/FriendsCollector.php`

### Retrieve tweets via 7 day search endpoint

https://developer.twitter.com/en/docs/twitter-api/v1/tweets/search/overview

**Endpoint:** `GET search/tweets`

Request-Body:
```json
{
    "q": "from:Beatrix_vStorch OR from:Alice_Weidel OR from:Tino_Chrupalla",
    "count": 100,
    "tweet_mode": "extended",
    "result_type": "recent",
    "include_entities": true
}
```

**Code:** `src/Collector/SearchCollector.php`

### Retrieve tweets via home timeline

https://developer.twitter.com/en/docs/twitter-api/v1/tweets/timelines/api-reference/get-statuses-home_timeline

**Endpoint:** `GET statuses/home_timeline`

**Code:** `src/Collector/TimelineCollector.php`

### Retrieve all tweets of a specific user with given user_id

https://developer.twitter.com/en/docs/twitter-api/v1/tweets/timelines/api-reference/get-statuses-user_timeline

**Endpoint:** `GET statuses/user_timeline`

Request-Body:
```json
{
    "user_id": "1234567890",
    "count": 200,
    "tweet_mode": "extended",
    "include_rts": true
}
```

**Code:** `src/Api/Twitter.php` `fetchTweetsByUserId()`
