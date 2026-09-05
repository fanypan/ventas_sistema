#!/bin/sh
set -eu

i=0
until mc alias set local http://minio:9000 "$MINIO_ROOT_USER" "$MINIO_ROOT_PASSWORD"; do
  i=$((i + 1))
  if [ "$i" -gt 30 ]; then
    echo "MinIO no respondió"
    exit 1
  fi
  sleep 1
done

mc mb -p "local/${MINIO_BUCKET_PUBLIC}" || true
mc mb -p "local/${MINIO_BUCKET_PRIVATE}" || true

mc anonymous set download "local/${MINIO_BUCKET_PUBLIC}"
mc anonymous set none "local/${MINIO_BUCKET_PRIVATE}"

echo "MinIO buckets listos: ${MINIO_BUCKET_PUBLIC} (público), ${MINIO_BUCKET_PRIVATE} (privado)"
