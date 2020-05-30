#!/bin/bash
cd "$(dirname "$0")"
source .env

now=$(date +"%Y-%m-%d_%H-%M-%S");
mkdir -p var/backup
docker-compose exec db mysqldump -u root -p$DB_ROOT_PASSWORD --no-create-info --insert-ignore twitter > var/backup/twitter-$now.sql
gzip var/backup/twitter-$now.sql
