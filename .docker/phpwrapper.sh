#!/bin/sh
docker-compose exec app php "$@"
return $?