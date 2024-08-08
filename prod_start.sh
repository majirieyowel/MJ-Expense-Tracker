#!/bin/bash

export APP_PORT=8050
export FORWARD_DB_PORT=3309
export WWWGROUP=1001
export WWWUSER=1001

docker compose -f docker-compose.prod.yml up -d