Cron jobs
=========
These scripts are intended to be run automatically, by cron jobs or scheduled
tasks. Manual editing may be required to suit the server configuration.

cron_esi.php: Fetch and post the killmails from ESI for all activated SSO keys
cron_clearup.php: Remove old files from the cache.
cron_feed.php: Fetch kills from other killboards
cron_value.php: Updated the values of items.
cron_zkb.php: Fetch kills from zKillboard APIs

#cron_cache.php: Fetch some commonly used files from the API to reduce the occasional slow page load.

Suggested timing of automated jobs:

Every 15 minutes (or more frequently, but not more often than every 5 minutes)
cron_feed.php: Fetch kills from other killboards (replaces cron_fetcher and cron_idfeed)
cron_zkb.php: Fetch kills from zKillboard APIs
cron_esi.php: Fetch kills from ESI

Daily
cron_clearup.php: Remove old files from the cache.
cron_value.php: Updated the values of items.
