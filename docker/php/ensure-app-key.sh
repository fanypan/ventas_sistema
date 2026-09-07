#!/bin/sh
# Sourced by php/queue/scheduler. Empty APP_KEY → one key on the storage volume.
cd /var/www/html
mkdir -p storage/app
KEY_FILE=storage/app/.app_key

if [ -n "${APP_KEY}" ] && [ "${APP_KEY}" != "base64:" ]; then
  if [ ! -s "$KEY_FILE" ]; then
    printf '%s\n' "$APP_KEY" > "$KEY_FILE"
    chmod 600 "$KEY_FILE" 2>/dev/null || true
  fi
elif [ -s "$KEY_FILE" ]; then
  APP_KEY=$(tr -d '\r\n' < "$KEY_FILE")
  echo "[entrypoint] APP_KEY leída del volumen"
else
  APP_KEY=$(php -r "echo 'base64:'.base64_encode(random_bytes(32));")
  printf '%s\n' "$APP_KEY" > "$KEY_FILE"
  chmod 600 "$KEY_FILE" 2>/dev/null || true
  echo "[entrypoint] APP_KEY generada sola (no hace falta pegarla en .env)"
fi

export APP_KEY
