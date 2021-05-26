#!/bin/bash
docker-compose down --remove-orphans
sudo rm -rf mysql
mkdir mysql
docker-compose build && docker-compose up -d
