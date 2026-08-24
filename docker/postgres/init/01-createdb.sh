#!/bin/bash
set -e
# POSTGRES_USER de la imagen oficial ya es superuser; CREATEDB queda explícito
# por si se cambia a un rol no superuser.
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
    ALTER ROLE "${POSTGRES_USER}" CREATEDB;
EOSQL
