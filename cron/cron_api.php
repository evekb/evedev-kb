#!/usr/bin/env php
<?php
/**
 * Cron Job for importing kills from the CCP XML API
 * If called with GET-parameter "feed" as API Key ID, only this key will be fetched,
 * otherwise all saved keys will be imported.
 * 
* Recommended frequency: once to four times per hour (depends on configured number of kills to fetch per run)
 * @package EDK
 */

// include the base class providing all context data
require_once('cron.base.php');

$cronStartTime = microtime(true);
logCron("Starting API Import");

// load mods
$page = 'cron_api';
event::setCron(TRUE);
loadMods($page);

$myEveAPI = new API_KillLog();
$myEveAPI->iscronjob_ = true;

$qry = new DBQuery();
$qry->execute("SELECT * FROM kb3_api_keys WHERE key_kbsite = '" . KB_SITE . "' ORDER BY key_name");
while ($row = $qry->getRow()) {
	// for fetching specific keys
        if(isset($_GET['feed']) && $_GET['feed'] && $row['key_id'] != $_GET['feed']) {
		continue;
	}
	logCron("Importing Mails for " . $row['key_name']);
	logCron($myEveAPI->Import($row['key_name'], $row['key_id'], $row['key_key'], $row['key_flags']));
}
logCron("Time taken = ".(microtime(true) - $cronStartTime)." seconds.");