#!/usr/bin/php
<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
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

$cronStartTime = microtime(true);
@error_reporting(E_ERROR);

if( php_sapi_name() == 'cli' ) {
	ob_implicit_flush(true);
}

// Has to be run from the KB main directory for nested includes to work
if(file_exists(getcwd().'/cron_idfeed.php'))
{
	// current working directory minus last 5 letters of string ("/cron")
	$KB_HOME = preg_replace('/[\/\\\\]cron$/', '', getcwd());
}
elseif(file_exists(__FILE__))
{
	$KB_HOME = preg_replace('/[\/\\\\]cron[\/\\\\]cron_idfeed\.php$/', '', __FILE__);
}
else die("Set \$KB_HOME to the killboard root in cron/cron_idfeed.php.");

// If the above doesn't work - place your working directory path to killboard root below - comment out the above two lines and uncomment the two below

// Edit the path below with your webspace directory to the killboard root folder - also check your php folder is correct as defined by the first line of this file
//$KB_HOME = "/home/yoursite/public_html/kb";

chdir($KB_HOME);

require_once('kbconfig.php');
require_once('globals.php');
require_once ('common/includes/class.edkerror.php');

set_error_handler(array('EDKError', 'handler'), E_ERROR );
$config = new Config(KB_SITE);
getID();

$feeds = config::get("fetch_idfeeds");

foreach($feeds as $key => &$val)
{
	// Just in case, check for empty urls.
	if(empty($val['url']))
		continue;

	printlog("Checking Feed URL: ".$val['url'] . " [Last Kill: " . $val['lastkill'] . "]");

	while( ($result = FetchKills($val)) !== false ) {
		$val['lastkill'] = $result[0];
		config::set("fetch_idfeeds", unserialize(serialize($feeds))); // ensure we get a 'clone' of feeds sub-arrays
		if($result[1] < 200 ) {
			break;
		}
	}
}

printlog("Time taken = ".(microtime(true) - $cronStartTime)." seconds.");

function FetchKills($val) {
	$feedfetch = new IDFeed();
	$feedfetch->setID();
	if($val['apikills'])
		$feedfetch->setAllKills(0);
	else
		$feedfetch->setAllKills(1);

	$feedfetch->setTrust($val['trusted']);

	if(!$val['lastkill'])
		$feedfetch->setStartDate(time() - 60*60*24*7);
	else if($val['apikills'])
		$feedfetch->setStartKill($val['lastkill'] + 1);
	else
		$feedfetch->setStartKill($val['lastkill'] + 1, true);

	if($feedfetch->read($val['url']) !== false)
	{
		if($val['apikills'] && intval($feedfetch->getLastReturned()) > $val['lastkill'])
			$id = intval($feedfetch->getLastReturned());
		else if(!$val['apikills'] && intval($feedfetch->getLastInternalReturned()) > $val['lastkill']) {
			$id = intval($feedfetch->getLastInternalReturned());
		} else {
			$id = $val['lastkill'];
		}
		$posted = count($feedfetch->getPosted());
		$skipped = count($feedfetch->getSkipped());
		$duplicate = count($feedfetch->getDuplicate());
		printlog( $posted." kills were posted, ".$duplicate." duplicate kills and ".$skipped." were skipped.");
		printlog( "Last kill ID returned was ".$id);
		return array($id, $skipped+$posted+$duplicate);
	}
	else
	{
		printlog( "Error reading feed: ".$val['url']);
		if(!$val['lastkill'])
			printlog(", Start time = ".(time() - 60*60*24*7));
		else if($val['apikills'])
			printlog(", Start kill = ".($val['lastkill']));
		printlog($feedfetch->errormsg());
		return false;
	}
}

function printlog($string) {
	if( php_sapi_name() != 'cli' ) {
		echo $string . "<br />\n";
	} else {
		echo $string . "\n";
	}
}

function getID()
{
	// Set pilot OR corporation OR alliance id
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
}
