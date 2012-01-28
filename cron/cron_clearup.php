#!/usr/bin/php
<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

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

if (!substr_compare(PHP_OS, 'win', 0, 3, true)) {
	@ini_set('include_path', ini_get('include_path').';.\\common\\includes');
} else {
	@ini_set('include_path', ini_get('include_path').':./common/includes');
}

if(file_exists(getcwd().'/cron_clearup.php')) {
	// current working directory minus last 5 letters of string ("/cron")
	$KB_HOME = preg_replace('/[\/\\\\]cron$/', '', getcwd());
} else if(file_exists(__FILE__)) {
	$KB_HOME = preg_replace('/[\/\\\\]cron[\/\\\\]cron_clearup\.php$/', '', __FILE__);
} else {
	echo "Set \$KB_HOME to the killboard root in cron/cron_clearup\.php.";
	die;
}

// If the above doesn't work - place your working directory path to killboard root below - comment out the above two lines and uncomment the two below

// Edit the path below with your webspace directory to the killboard root folder - also check your php folder is correct as defined by the first line of this file
//$KB_HOME = "/home/yoursite/public_html/kb";

chdir($KB_HOME);

require_once('kbconfig.php');
require_once('common/includes/globals.php');
require_once('common/includes/db.php');
require_once ('common/includes/class.edkerror.php');

set_error_handler(array('EDKError', 'handler'), E_ERROR );
@error_reporting(E_ERROR);

// disable query caching while the script is running.
$qcache = config::get('cfg_qcache');
if($qcache) {
	echo "<br />\n File query cache disabled <br />\n";
	config::set('cfg_qcache', 0);
}
$pcache = config::get('cache_enabled');
if($pcache) {
	echo "Page cache disabled <br />\n";
	config::set('cache_enabled', 0);
}

echo "<br />Removed ".CacheHandler::removeByAge('SQL/', $maxSQLAge * 24)." files from SQL/<br />\n";
echo "Removed ".CacheHandler::removeByAge('page/'.KB_SITE.'/', $maxOtherAge * 24)." files from page/<br />\n";
echo "Removed ".CacheHandler::removeByAge("templates_c/", $maxOtherAge * 24)." files from templates_c/<br />\n";
echo "Removed ".CacheHandler::removeByAge("mail/", $maxOtherAge * 24)." files from mail/<br />\n";
// Let's let people see their latest beautiful creation in the character creator.
echo "Removed ".CacheHandler::removeByAge('img/', $maxImageAge * 24)." files from img/<br />\n";
//echo "Removed ".CacheHandler::removeBySize('img/', 512 * 24)." files from img/<br />\n";
//echo "Removed ".CacheHandler::removeByAge('store/', 7 * 24)." files from store/<br />\n";
echo "Removed ".CacheHandler::removeBySize('store/', $maxStoreSize)." files from store/<br />\n";
echo "Removed ".CacheHandler::removeByAge('api/', $maxAPIAge * 24)." files from api/<br />\n";

//echo "Removed ".CacheHandler::removeByAge('/', 30 * 24, false)." files from entire cache<br />\n";

if($qcache) {
	echo "<br />\n File query cache re-enabled <br />\n";
	config::set('cfg_qcache', 1);
}
if($pcache) {
	echo "Page cache re-enabled <br />\n";
	config::set('cache_enabled', 1);
}