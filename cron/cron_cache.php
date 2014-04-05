#!/usr/bin/php
<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

// check your php folder is correct as defined by the first line of this file
@error_reporting(E_ALL ^ E_NOTICE);
//
// Simple Cronjob script - set it to run this, no more than once an hour as you can only pull info once an hour anyway
// by Captain Thunk! (ISK donations are all gratefully received)
//
if (!substr_compare(PHP_OS, 'win', 0, 3, true))
{
	@ini_set('include_path', ini_get('include_path').';.\\common\\includes');
}
else
{
	@ini_set('include_path', ini_get('include_path').':./common/includes');
}

if(function_exists("set_time_limit"))
	@set_time_limit(0);

if(file_exists(getcwd().'/cron_cache.php')) {
	// current working directory minus last 5 letters of string ("/cron")
	$KB_HOME = preg_replace('/[\/\\\\]cron$/', '', getcwd());
} else if(file_exists(__FILE__)) {
	$KB_HOME = preg_replace('/[\/\\\\]cron[\/\\\\]cron_cache\.php$/', '', __FILE__);
} else {
	echo "Set \$KB_HOME to the killboard root in cron/cron_cache.php.";
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

$outhead = "Running Cron_Cache on " . gmdate("M d Y H:i") . "\n\n";
$html = '';

// Alliance
$myAlliAPI = new API_Alliance();
$Allitemp .= $myAlliAPI->fetchalliances();
$html .= "Caching Alliance XML \n";

if ($html)
{
	$html = str_replace("<div class=block-header2>","",$html);
	$html = str_replace("</div>","\n",$html);
	$html = str_replace("<br>","\n",$html);
 
	//print $outhead . strip_tags($out, '<a>');
    print $outhead . strip_tags($html);
}
