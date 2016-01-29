#!/usr/bin/env php
<?php
/**
 * Cron Job updating the cached alliance list. Not essential.
 * 
 * Recommended frequency: once a day
 * @package EDK
 */

// include the base class providing all context data
require_once('cron.base.php');

logCron("Starting Alliance list update");
$cronStartTime = microtime(true);

// Alliance
$allianceApi = new API_Alliance();
$allianceApi->fetchalliances();
if(!is_null($allianceApi->getError()))
{
    logCron("Error occurred while fetching Alliance list:");
    logCron($allianceApi->getMessage());
}

else
{
    logCron("Finished successfully");
}

logCron('Time taken = '.(microtime(true) - $cronStartTime).' seconds.');