#!/bin/bash

export APP_PORT=8050
export FORWARD_DB_PORT=3309
export WWWGROUP=1000
export WWWUSER=1000

docker compose -f docker-compose.prod.yml up -d