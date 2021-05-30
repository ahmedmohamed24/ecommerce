#!/bin/bash
docker-compose down --remove-orphans
sudo rm -rf mysql
mkdir mysql
docker-compose build && docker-compose up -d
#docker-compose exec php php /var/www/html/artisan migrate:fresh --seed
echo "run migration to get started"
