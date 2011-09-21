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

if(file_exists(getcwd().'/cron_import.php'))
{
	// current working directory minus last 5 letters of string ("/cron")
	$KB_HOME = preg_replace('/[\/\\\\]cron$/', '', getcwd());
}
elseif(file_exists(__FILE__))
{
	$KB_HOME = preg_replace('/[\/\\\\]cron[\/\\\\]cron_import\.php$/', '', __FILE__);
}
else die("Set \$KB_HOME to the killboard root in cron/cron_import.php.");

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
$ApiCache = new ApiCache(KB_SITE);

define('KB_TITLE', config::get('cfg_kbtitle'));

if (config::get('cfg_pilotid'))
{
	if(!is_array(config::get('cfg_pilotid'))) config::set('cfg_pilotid',array(config::get('cfg_pilotid')));
	foreach(config::get('cfg_pilotid') as $val)
	{
		define('PILOT_ID', $val );
		break;
	}
	define('CORP_ID', 0);
	define('ALLIANCE_ID', 0);
}
elseif (config::get('cfg_corpid'))
{
	define('PILOT_ID', 0);
	if(!is_array(config::get('cfg_corpid'))) config::set('cfg_corpid',array(config::get('cfg_corpid')));
	foreach(config::get('cfg_corpid') as $val)
	{
		define('CORP_ID', $val );
		break;
	}
	define('CORP_ID', intval(config::get('cfg_corpid')));
	define('ALLIANCE_ID', 0);
}
elseif(config::get('cfg_allianceid'))
{
	define('PILOT_ID', 0);
	define('CORP_ID', 0);
	if(!is_array(config::get('cfg_allianceid'))) config::set('cfg_allianceid',array(config::get('cfg_allianceid')));
	foreach(config::get('cfg_allianceid') as $val)
	{
		define('ALLIANCE_ID', $val );
		break;
	}
}
else
{
	define('PILOT_ID', 0);
	define('CORP_ID', 0);
	define('ALLIANCE_ID', 0);
}

if (!$dir = config::get('cache_dir'))
{
    $dir = 'cache/data';
}
define('KB_CACHEDIR', $dir);

$outhead = "Running API Mod ". APIVERSION . " on " . gmdate("M d Y H:i") . "\n\n";
$out = '';

$myEveAPI = new API_KillLog();
$myEveAPI->iscronjob_ = true;

$keycount = config::get('API_Key_count');

$i = 1;
if(isset($_GET['feed']))
{
	$i = intval($_GET['feed']);
	if(!$i) $i = 1;
	elseif($keycount > $i) $keycount = $i;
}
elseif(isset($argv[0]))
{
	foreach($argv as $arg)
	{
		if(substr($arg, 0, 5) == "feed=")
		{
			$i = intval(substr($arg,5));
			if(!$i) $i = 1;
			elseif($keycount > $i) $keycount = $i;
		}
	}
}
for (; $i <= $keycount; $i++)
{
    $keyindex = $i;
    $myEveAPI->Output_ = "Importing Mails for " . $config->get("API_Name_" . $i);
	$myEveAPI->Output_ .= "\n";
    $typestring = $config->get("API_Type_" . $i);
    $keystring = 'userID=' . $config->get('API_UserID_' . $i) . '&apiKey=' . $config->get('API_Key_' . $i) . '&characterID=' . $config->get('API_CharID_' . $i);
	$myEveAPI->cachetext_ = "";
	$myEveAPI->cacheflag_ = false;
    $outtemp .= $myEveAPI->Import($keystring, $typestring, $keyindex);
	//config::set('API_CachedUntil_' . $keyindex, $myEveAPI->cachetext_);
}
$out = $outtemp;

if ($out)
{
    $out = str_replace("<div class=block-header2>","",$out);
    $out = str_replace("</div>","\n",$out);
    $out = str_replace("<br>","\n",$out);

    //print $outhead . strip_tags($out, '<a>');
    print $outhead . strip_tags($out);
}
echo "Time taken = ".(microtime(true) - $cronStartTime)." seconds.";
