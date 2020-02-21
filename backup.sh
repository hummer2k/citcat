now=$(date +"%Y-%m-%d");
docker-compose exec db mysqldump -u root -proot twitter > twitter-$now.sql
gzip twitter-$now.sql

