#!/bin/bash
cd "$(dirname "$0")"
docker-compose up -d

if [[ "$1" = "timeline" ]]
then
    docker-compose exec -T php bin/console twitter:collect:timeline -q
elif [[ "$1" = "reindex" ]]
then
    docker-compose exec -T php bin/console twitter:tweets:reindex --from-date=-2hours
else
    docker-compose exec -T php bin/console twitter:collect:search --result_type=mixed
    docker-compose exec -T php bin/console twitter:collect:search --result_type=recent
fi
