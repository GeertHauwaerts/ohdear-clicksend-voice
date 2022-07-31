#!/bin/bash

if [ ! -f ".env" ]; then
  cp .env.default .env
fi

if [ -d "vendor" ]; then
  composer update
else
  composer install
fi

set -o allexport
source .env
set +o allexport

until nc -z -v -w 30 ${REDIS_HOSTNAME} 6379; do
  echo "Waiting for Redis..."
  sleep 1
done

tail -f /dev/null
