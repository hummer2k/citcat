#!/usr/bin/env bash
chmod 0777 ./var/vol/elasticsearch/
ln -sf docker-compose.dev.yml docker-compose.override.yml
docker-compose up -d
docker-compose exec php bin/composer install

echo "= Services ======================================================"
echo ""
echo "  Web-Interface: http://localhost:8005"
echo "  Kibana:        http://localhost:5601 (may take a while to load)"
echo "  phpMyAdmin:    http://localhost:8040"
echo ""
echo "================================================================="
echo ""
