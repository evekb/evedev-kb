#!/usr/bin/php
<?php

@error_reporting(E_ERROR);

if (!substr_compare(PHP_OS, 'win', 0, 3, true))
{
	@ini_set('include_path', ini_get('include_path').';.\\common\\includes');
}
else
{
	@ini_set('include_path', ini_get('include_path').':./common/includes');
}

$cronStartTime = microtime(true);

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
$config = new Config(KB_SITE);
getID();

$feeds = config::get("fetch_idfeeds");
$html = '';

foreach($feeds as $key => &$val)
{
	// Just in case, check for empty urls.
	if(empty($val['url'])) continue;
	$feedfetch = new IDFeed();
	$feedfetch->setID();
	if($val['apikills']) $feedfetch->setAllKills(0);
	else $feedfetch->setAllKills(1);
	$feedfetch->setTrust($val['trusted']);
	if(!$val['lastkill']) $feedfetch->setStartDate(time() - 60*60*24*7);
	else if($val['apikills']) $feedfetch->setStartKill($val['lastkill']);
	else $feedfetch->setStartKill($val['lastkill'], true);

	if($feedfetch->read($val['url']) !== false)
	{
		if($val['apikills'] && intval($feedfetch->getLastReturned()) > $val['lastkill'])
			$val['lastkill'] = intval($feedfetch->getLastReturned());
		else if(!$val['apikills'] && intval($feedfetch->getLastInternalReturned()) > $val['lastkill'])
			$val['lastkill'] = intval($feedfetch->getLastInternalReturned());
		$html .= "Feed: ".$val['url']."<br />\n";
		$html .= count($feedfetch->getPosted())." kills were posted and ".
			count($feedfetch->getSkipped())." were skipped.<br />\n";
		$html .= "Last kill ID returned was ".$val['lastkill']."<br />\n";
		config::set("fetch_idfeeds", $feeds);
	}
	else
	{
		$html .= "Error reading feed: ".$val['url'];
		if(!$val['lastkill']) $html .= ", Start time = ".(time() - 60*60*24*7);
		else if($val['apikills']) $html .= ", Start kill = ".($val['lastkill']);
		$html .= $feedfetch->errormsg();
	}
}
echo $html."<br />\n";

echo "Time taken = ".(microtime(true) - $cronStartTime)." seconds.";

function getID()
{
	// Set pilot OR corporation OR alliance id
	if (config::get('cfg_pilotid'))
	{
		define('PILOT_ID', intval(config::get('cfg_pilotid')) );
		define('CORP_ID', 0);
		define('ALLIANCE_ID', 0);
	}
	elseif (config::get('cfg_corpid'))
	{
		define('PILOT_ID', 0);
		define('CORP_ID', intval(config::get('cfg_corpid')));
		define('ALLIANCE_ID', 0);
	}
	elseif(config::get('cfg_allianceid'))
	{
		define('PILOT_ID', 0);
		define('CORP_ID', 0);
		define('ALLIANCE_ID', intval(config::get('cfg_allianceid')));
	}
	else
	{
		define('PILOT_ID', 0);
		define('CORP_ID', 0);
		define('ALLIANCE_ID', 0);
	}
}
