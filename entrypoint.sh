#!/bin/bash

# Important don't delete
echo "Running fpm to handle connections from nginx"
php-fpm

# Start the run once job.
echo "Docker container has been started"

echo "Install dependencies"
composer install

echo "Install database"
php /usr/src/app/bin/console doctrine:migrations:migrate -n

echo "Setup cron jobs"
# Setup a cron schedule
echo "
* * * * * /usr/local/bin/php /usr/src/app/bin/console app:refresh-manga >> /var/log/cron_refresh.log 2>&1
# This extra line makes it a valid cron" > scheduler.txt

echo "Add cron jobs"
crontab scheduler.txt
cron -f