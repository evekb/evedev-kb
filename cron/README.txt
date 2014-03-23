Cron jobs
=========
These scripts are intended to be run automatically, by cron jobs or scheduled
tasks. Manual editing may be required to suit the server configuration.

cron_api.php: Fetch the killlog from CCP's API and post new kills
cron_clearup.php: Remove old files from the cache.
cron_feed.php: Fetch kills from other killboards (replaces cron_fetcher and cron_idfeed)
cron_value.php: Updated the values of items.

#cron_cache.php: Fetch some commonly used files from the API to reduce the occasional slow page load.

Suggested timing of automated jobs:
Hourly:
cron_api.php: Fetch the killlog from CCP's API and post new kills
cron_feed.php: Fetch kills from other killboards (replaces cron_fetcher and cron_idfeed)

Daily
cron_clearup.php: Remove old files from the cache.

Weekly
cron_value.php: Updated the values of items.

(Not essential)
cron_cache.php: Fetch some commonly used files from the API to reduce the occasional slow page load.

