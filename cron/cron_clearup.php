#!/usr/bin/php
<?php
/**
 * @package EDK
 */

if (!substr_compare(PHP_OS, 'win', 0, 3, true))
{
	@ini_set('include_path', ini_get('include_path').';.\\common\\includes');
}
else
{
	@ini_set('include_path', ini_get('include_path').':./common/includes');
}

if(file_exists(getcwd().'/cron_clearup.php'))
{
	// current working directory minus last 5 letters of string ("/cron")
	$KB_HOME = preg_replace('/[\/\\\\]cron$/', '', getcwd());
}
elseif(file_exists(__FILE__))
{
	$KB_HOME = preg_replace('/[\/\\\\]cron[\/\\\\]cron_clearup\.php$/', '', __FILE__);
}
else die("Set \$KB_HOME to the killboard root in cron/cron_clearup.php.");

// If the above doesn't work - place your working directory path to killboard root below - comment out the above two lines and uncomment the two below

// Edit the path below with your webspace directory to the killboard root folder - also check your php folder is correct as defined by the first line of this file
//$KB_HOME = "/home/yoursite/public_html/kb";

chdir($KB_HOME);

require_once('kbconfig.php');
require_once('common/includes/class.edkloader.php');
require_once('common/includes/globals.php');
require_once('common/includes/db.php');

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

echo "<br />Removed ".CacheHandler::removeByAge('SQL/', 7 * 24)." files from SQL/<br />\n";
echo "Removed ".CacheHandler::removeByAge('page/'.KB_SITE.'/', 7 * 24)." files from page/<br />\n";
echo "Removed ".CacheHandler::removeByAge("templates_c/", 1 * 24)." files from templates_c/<br />\n";
echo "Removed ".CacheHandler::removeByAge("mail/", 7 * 24)." files from mail/<br />\n";
// Let's let people see their latest beautiful creation in the character creator.
echo "Removed ".CacheHandler::removeByAge('img/', 30 * 24)." files from img/<br />\n";
echo "Removed ".CacheHandler::removeByAge('store/', 7 * 24)." files from store/<br />\n";
echo "Removed ".CacheHandler::removeByAge('/', 30 * 24, false)." files from entire cache<br />\n";

if($qcache) {
	echo "<br />\n File query cache re-enabled <br />\n";
	config::set('cfg_qcache', 1);
}
if($pcache) {
	echo "Page cache re-enabled <br />\n";
	config::set('cache_enabled', 1);
}