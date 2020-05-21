#!/usr/bin/env bash
now=$(date +"%Y-%m-%d");
docker-compose exec db mysqldump -u root -proot --no-create-info --insert-ignore twitter > twitter-$now.sql
gzip twitter-$now.sql

