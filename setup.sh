#!/usr/bin/env bash
docker-compose up -d
docker-compose exec php composer install
