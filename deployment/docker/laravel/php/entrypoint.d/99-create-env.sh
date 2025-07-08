#!/bin/sh

APP_DIR="/var/www/html"

# Create the .env file
# Replacing 'command <CR> if [ $? -eq 0 ]; then' with:
if envsubst < "$APP_DIR/.env.example.production" > "$APP_DIR/.env";
then
    echo "Success: The .env file has been created."
else
    echo "Error: Failed to create the .env file."
fi

# Run migrations
sleep 10
echo "Running migrations..."
cd "$APP_DIR" || exit 1 # Should fail if path not found.
if php artisan about > /dev/null 2> /dev/null;
then
    php artisan down
    # This resets the process (resets the databases!):
    # php artisan migrate:fresh --seed
    # This runs the basic migration (safe to do):
    # @todo 1. uncomment line 26 when migration is done on each server!
    # @todo 2. delete these 2 todos.
    # php artisan migrate --force
    php artisan config:cache
    php artisan up
    echo "Migrations complete."
else
    echo "Error: Unable to execute artisan commands"
    exit 1
fi
