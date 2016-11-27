#!/usr/bin/env php
<?php
/**
 * Cron Job updating item values from CREST.
  * 
 * Recommended frequency: once a day
 * 
 * @package EDK
 */

// include the base class providing all context data
require_once('cron.base.php');

// load mods
$page = 'crestValueFetch';
event::setCron(TRUE);
loadMods($page);

$fetch = new ValueFetcherEsi();
// Fetch
$count = $fetch->fetchValues();

// log result
logCron($count." Items updated");
logCron('Time taken = '.(microtime(true) - $cronStartTime).' seconds.');
