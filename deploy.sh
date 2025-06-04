#!/bin/bash

# Load environment variables from .env
if [ -f .env ]; then
    export $(grep -v '^#' .env | xargs)
fi

# Safety check: abort if DEPLOY_PATH doesn't contain /var/www
if [[ "$DEPLOY_PATH" != *"/var/www"* ]]; then
    echo "Error: DEPLOY_PATH must contain '/var/www' for safety. Current value: $DEPLOY_PATH"
    exit 1
fi

docker compose exec app npm i
docker compose exec app npm run build

# Get the directory where this script is located and build absolute path
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
scp -r "$SCRIPT_DIR/public/build/." "$DEPLOY_SERVER:$DEPLOY_PATH"
