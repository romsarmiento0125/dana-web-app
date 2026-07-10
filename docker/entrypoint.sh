#!/usr/bin/env sh
set -eu

APP_DIR="/var/www/html"
cd "$APP_DIR"

# Ensure CI4 writable paths are usable even when writable is a mounted volume.
if [ -d "$APP_DIR/writable" ]; then
  chown -R www-data:www-data "$APP_DIR/writable" || true
  chmod -R 775 "$APP_DIR/writable" || true
fi

# Optional migration on container start.
# Set RUN_MIGRATIONS=false to skip this behavior.
RUN_MIGRATIONS="${RUN_MIGRATIONS:-true}"
if [ "$RUN_MIGRATIONS" = "true" ] && [ -f "$APP_DIR/spark" ]; then
  echo "[entrypoint] Running database migrations..."

  # Retry to tolerate brief DB startup lag.
  i=1
  max=15
  until php spark migrate --all --no-interaction; do
    if [ "$i" -ge "$max" ]; then
      echo "[entrypoint] Migration failed after $max attempts. Continuing startup."
      break
    fi

    echo "[entrypoint] Migration attempt $i failed. Retrying in 3s..."
    i=$((i + 1))
    sleep 3
  done
fi

exec "$@"
