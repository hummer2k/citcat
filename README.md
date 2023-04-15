# CITCAT: Content Indexing Tool for Collecting and Archiving Tweets

## Requirements

- Linux or Mac (Tested with ubuntu 22.04 LTS)
- Docker (Tested with 23.0.1)
- Docker-Compose (Tested with 1.29.2)

## Installation

1. Copy `.env.dist` to `.env` and add your Twitter API credentials.
    ```
    TWITTER_CONSUMER_KEY=
    TWITTER_CONSUMER_SECRET=
    TWITTER_OAUTH_TOKEN=
    TWITTER_OAUTH_TOKEN_SECRET=
    ```
   Please refer to https://developer.twitter.com/en/docs/twitter-api/getting-started/getting-access-to-the-twitter-api
2. Replace `FIXUID` and `FIXGID` with your actual User-ID. (Output of `echo $UID`)
    ```
    FIXUID=1001
    FIXGID=1001
    ```
3. Run the `setup.sh` script.

To stop the project run: `$ docker-compose down`

## Services

- Web-Interface: http://localhost:8005
- Kibana: http://localhost:5601
- phpMyAdmin: http://localhost:8040

## Collect Tweets

### Collect from timeline

`$ docker-compose exec php bin/console twitter:collect:timeline`

### Collect with search api

`$ docker-compose exec php bin/console twitter:collect:search`

## Import followers/friends

`$ docker-compose exec php bin/console twitter:collect:friends`


