Cron jobs
=========
These scripts are intended to be run automatically, by cron jobs or scheduled
tasks. Manual editing may be required to suit the server configuration.

cron_api.php: Fetch the killlog from CCP's API and post new kills
cron_clearup.php: Remove old files from the cache.
cron_feed.php: Fetch kills from other killboards (replaces cron_fetcher and cron_idfeed)
cron_value.php: Updated the values of items.

#cron_cache.php: Fetch some commonly used files from the API to reduce the occasional slow page load.

#cron_fetcher.php: Fetch kills from other killboards using RSS feeds)
#cron_idfeed.php: Fetch kills from other killboards using IDFeeds)
#cron_import.php: Fetch the oldstyle killlog from CCP's API


Suggested timing of automated jobs:
Hourly:
cron_api.php: Fetch the killlog from CCP's API and post new kills
cron_feed.php: Fetch kills from other killboards (replaces cron_fetcher and cron_idfeed)

(Only run these if the legacy support is needed)
cron_fetcher.php: Fetch kills from other killboards using RSS feeds)
cron_idfeed.php: Fetch kills from other killboards using IDFeeds)
cron_import.php: Fetch the oldstyle killlog from CCP's API

Daily
cron_clearup.php: Remove old files from the cache.

(Not essential)
cron_cache.php: Fetch some commonly used files from the API to reduce the occasional slow page load.

Weekly
cron_value.php: Updated the values of items.

