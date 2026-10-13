#!/bin/sh
# Generate APP_KEY at container startup if not provided via environment.
# This keeps secrets out of versioned files while allowing docker compose up --build
# to work without a .env file.
if [ -z "$APP_KEY" ]; then
    export APP_KEY=$(php artisan key:generate --show --no-ansi)
fi

exec php artisan serve --host=0.0.0.0 --port=8000
