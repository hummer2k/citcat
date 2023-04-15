#!/usr/bin/env bash
docker network create dockerdev-proxy
ln -s docker-compose.dev.yml docker-compose.override.yml
docker-compose up -d
docker-compose exec php bin/composer install
