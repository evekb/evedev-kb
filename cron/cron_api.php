#!/usr/bin/php
<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

// check your php folder is correct as defined by the first line of this file

@error_reporting(E_ERROR);
//
// Simple Cronjob script - set it to run this, no more than once an hour as you can only pull info once an hour anyway
// by Captain Thunk! (ISK donations are all gratefully received)
//

if(function_exists("set_time_limit"))
	@set_time_limit(0);

if (!substr_compare(PHP_OS, 'win', 0, 3, true))
{
	@ini_set('include_path', ini_get('include_path').';.\\common\\includes');
}
else
{
	@ini_set('include_path', ini_get('include_path').':./common/includes');
}

$cronStartTime = microtime(true);

if  (file_exists(getcwd().'/cron_api.php')) {
	// current working directory minus last 5 letters of string ("/cron")
	$KB_HOME = preg_replace('/[\/\\\\]cron$/', '', getcwd());
} else if (file_exists(__FILE__)) {
	$KB_HOME = preg_replace('/[\/\\\\]cron[\/\\\\]cron_api.php$/', '', __FILE__);
} else {
	echo "Set \$KB_HOME to the killboard root in cron/cron_api.php.";
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

$config = new Config(KB_SITE);

define('KB_TITLE', config::get('cfg_kbtitle'));

if (!$dir = config::get('cache_dir'))
{
    $dir = 'cache/data';
}
define('KB_CACHEDIR', $dir);

$outhead = "Running API Import on " . gmdate("M d Y H:i") . "\n\n";
$html = '';
$outtemp = '';

$myEveAPI = new API_KillLog();
$myEveAPI->iscronjob_ = true;

$qry = new DBQuery();
$qry->execute("SELECT * FROM kb3_api_keys WHERE key_kbsite = '" . KB_SITE . "' ORDER BY key_name");
while ($row = $qry->getRow()) {
	if(isset($_GET['feed']) && $_GET['feed'] && $row['key_id'] != $_GET['feed']) {
		continue;
	}
	$html .= "Importing Mails for " . $row['key_name'] . "<br />";
	$html .= $myEveAPI->Import($row['key_name'], $row['key_id'], $row['key_key'], $row['key_flags']);
	$apicachetime[$i] = $myEveAPI->CachedUntil_;
}
$html .= "Time taken = ".(microtime(true) - $cronStartTime)." seconds.";

$html = $outhead.$html;
if (php_sapi_name() == 'cli') {
	$html = str_replace("</div>","</div>\n",$html);
	$html = str_replace("<br>","\n",$html);
	$html = str_replace("<br />","\n",$html);
	$html = strip_tags($html);
}
echo $html."\n";
