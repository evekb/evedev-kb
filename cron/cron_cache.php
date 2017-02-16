#!/usr/bin/env php
<?php
/**
 * Cron Job updating the cached alliance list. Not essential.
 * 
 * Recommended frequency: once a day
 * @package EDK
 * @deprecated
 */

// include the base class providing all context data
require_once('cron.base.php');

logCron("Starting Alliance list update");
$cronStartTime = microtime(true);
// DELETE ME
logCron("This cronjob is deprecated!");

logCron('Time taken = '.(microtime(true) - $cronStartTime).' seconds.');