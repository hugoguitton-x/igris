#!/bin/bash
DIR=/usr/src/app/docker-entrypoint.d
echo "Entrypoint execution ..."
if [[ -d "$DIR" ]]; then /bin/run-parts --verbose "$DIR"; fi
echo "Starting CMD execution : $@ ..."
exec "$@"
