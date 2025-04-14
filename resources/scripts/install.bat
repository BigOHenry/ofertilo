@echo off
cd ..\..
docker network create ofertilo_net
docker-compose up -d
echo Application starts on http://localhost:8080