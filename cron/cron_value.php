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

$cronStartTime = microtime(true);
logCron("Starting CREST item value update");

// load mods
$page = 'crestValueFetch';
event::setCron(TRUE);
loadMods($page);

// get the configured CREST URL
$url = config::get('itemPriceCrestUrl');
if ($url == null || $url == "")
{
	$url = CREST_PUBLIC_URL . ValueFetcherCrest::$CREST_PRICES_ENDPOINT;
}

$fetch = new ValueFetcherCrest($url);
// Fetch
$count = $fetch->fetchValues();

// log result
logCron($count." Items updated");
logCron('Time taken = '.(microtime(true) - $cronStartTime).' seconds.');