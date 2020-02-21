#!/bin/bash

docker-compose up -d
docker-compose exec php bin/console twitter:collect:search --result_type=mixed
docker-compose exec php bin/console twitter:collect:search --result_type=recent
#docker-compose exec php bin/console twitter:collect:timeline

