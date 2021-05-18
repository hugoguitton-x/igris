#!/bin/bash

# Start the run once job.
echo "Docker container has been started"

echo "Install dependencies"
composer install

echo "Install database"
php /usr/src/app/bin/console doctrine:migrations:migrate -n

echo "Setup cron jobs"
# Setup a cron schedule
echo "
0 */2 * * * /usr/local/bin/php /usr/src/app/bin/console app:refresh-infos-manga >> /var/log/cron_refresh_infos_manga.log 2>&1
# 0 18 * * * /usr/local/bin/php /usr/src/app/bin/console app:refresh-image-manga >> /var/log/cron_refresh_image_manga.log 2>&1
# This extra line makes it a valid cron" > scheduler.txt

echo "Add cron jobs"
crontab scheduler.txt
cron -f &

# Important don't delete
echo "Running fpm to handle connections from nginx"
php-fpm
