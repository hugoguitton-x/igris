#!/bin/bash

# Start the run once job.
echo "Docker container has been started"

# Setup a cron schedule
echo "5 * * * * php /usr/src/app/bin/console app:refresh-manga >> /var/log/cron_refresh.log 2>&1
* * * * * echo \"Hello world\" >> /var/log/cron_hello.log 2>&1
* * * * * php -v >> /var/log/cron_php.log 2>&1
# This extra line makes it a valid cron" > scheduler.txt

crontab scheduler.txt
cron -f