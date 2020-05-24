#!/bin/bash
cd "$(dirname "$0")"
docker-compose up -d

now=$(date +"%Y-%m-%d_%T");
mkdir -p var/backup
docker-compose exec db mysqldump -u root -proot --no-create-info --insert-ignore twitter > var/backup/twitter-$now.sql
gzip twitter-$now.sql

