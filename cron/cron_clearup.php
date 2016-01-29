#!/usr/bin/env php
<?php
/**
 * Cron Job for clearing the various caches
 * 
 * Recommended frequency: once a day
 * @package EDK
 */

// include the base class providing all context data
require_once('cron.base.php');

logCron("Starting cache clearup");
$cronStartTime = microtime(true);

/** @var integer Maximum size for the store in megabytes. */
$maxStoreSize = 512;
/** @var integer Maximum image age in days. */
$maxImageAge = 30;
/** @var integer Maximum API cache age in days. */
$maxAPIAge = 1;
/** @var integer Maximum SQL query age in days. */
$maxSQLAge = 2;
/** @var integer Maximum cache age for everything else in days. */
$maxOtherAge = 7;


// disable query caching while the script is running.
$qcache = config::get('cfg_qcache');
if($qcache) {
	logCron("File query cache disabled");
	config::set('cfg_qcache', 0);
}
$pcache = config::get('cache_enabled');
if($pcache) 
{
	logCron("Page cache disabled");
	config::set('cache_enabled', 0);
}

logCron("clearing SQL query cache...");
logCron("Removed ".CacheHandler::removeByAge('SQL/', $maxSQLAge * 24)." files from SQL/");

logCron("clearing page cache...");
logCron("Removed ".CacheHandler::removeByAge('page/'.KB_SITE.'/', $maxOtherAge * 24)." files from page/");

logCron("clearing Smarty templates cache...");
logCron("Removed ".CacheHandler::removeByAge("templates_c/", $maxOtherAge * 24)." files from templates_c/");

logCron("clearing killmail cache...");
logCron("Removed ".CacheHandler::removeByAge("mails/", $maxOtherAge * 24)." files from mail/");

logCron("clearing image cache...");
logCron("Removed ".CacheHandler::removeByAge('img/', $maxImageAge * 24)." files from img/");

logCron("clearing object cache...");
logCron("Removed ".CacheHandler::removeBySize('store/', $maxStoreSize)." files from store/");

logCron("clearing API cache...");
logCron("Removed ".CacheHandler::removeByAge('api/', $maxAPIAge * 24)." files from api/");

if($qcache) 
{
	logCron("File query cache re-enabled");
	config::set('cfg_qcache', 1);
}
if($pcache) 
{
	logCron("Page cache re-enabled");
	config::set('cache_enabled', 1);
}
logCron('Time taken = '.(microtime(true) - $cronStartTime).' seconds.');