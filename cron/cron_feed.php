#!/usr/bin/php
<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

if (!substr_compare(PHP_OS, 'win', 0, 3, true)) {
	@ini_set('include_path', ini_get('include_path').';.\\common\\includes');
} else {
	@ini_set('include_path', ini_get('include_path').':./common/includes');
}

if(function_exists("set_time_limit"))
	@set_time_limit(0);
@ini_set('memory_limit', '1024M');

$cronStartTime = microtime(true);
@error_reporting(E_ERROR);

// Has to be run from the KB main directory for nested includes to work
if(file_exists(getcwd().'/cron_feed.php')) {
	// current working directory minus last 5 letters of string ("/cron")
	$KB_HOME = preg_replace('/[\/\\\\]cron$/', '', getcwd());
} else if(file_exists(__FILE__)) {
	$KB_HOME = preg_replace('/[\/\\\\]cron[\/\\\\]cron_feed\.php$/', '', __FILE__);
} else {
	echo "Set \$KB_HOME to the killboard root in cron/cron_feed\.php.";
	die;
}

// If the above doesn't work - place your working directory path to killboard root below - comment out the above two lines and uncomment the two below

// Edit the path below with your webspace directory to the killboard root folder - also check your php folder is correct as defined by the first line of this file
//$KB_HOME = "/home/yoursite/public_html/kb";

chdir($KB_HOME);

require_once('kbconfig.php');
require_once('globals.php');
$config = new Config(KB_SITE);

$feeds = config::get("fetch_idfeeds");
$html = '';
$page = 'idfeedsyndication';

// load mods
event::setCron(TRUE);
loadMods($page);

foreach($feeds as $key => &$val) {
	$tmphtml = '';
        if ($tmphtml = getIDFeed($key, $val)) {
                $html .= "Fetching IDFeed: ".$key."<br />\n".$tmphtml;
        }
}
echo $html."<br />\n";

echo "Time taken = ".(microtime(true) - $cronStartTime)." seconds.\n";

/**
 * Fetch the board owners.
 * @return array Array of id strings to add to URLS
 */
function getOwners()
{
	$myids = array();
	if(!defined('MASTER') || !MASTER) {
		foreach(config::get('cfg_pilotid') as $entity) {
			$pilot = new Pilot($entity);
			$myids[] = '&pilot=' . urlencode($pilot->getName());
		}

		foreach(config::get('cfg_corpid') as $entity) {
			$corp = new Corporation($entity);
			$myids[] = '&corp=' . urlencode($corp->getName());
		}
		foreach(config::get('cfg_allianceid') as $entity) {
			$alli = new Alliance($entity);
			$myids[] = '&alli=' . urlencode($alli->getName());
		}
	}
	return $myids;
}

function getIDFeed(&$key, &$val)
{
	$html = '';
	// Just in case, check for empty urls.
	if(empty($val['url'])) {
		return '';
	}
	$feedfetch = new IDFeed();
	$feedfetch->setID();
	$feedfetch->setAllKills(1);

	if(!$val['lastkill']) {
		$feedfetch->setStartDate(time() - 60*60*24*7);
	} else {
		$feedfetch->setStartKill($val['lastkill'] + 1, true);
	}

	if($feedfetch->read($val['url']) !== false) {
                if(intval($feedfetch->getLastInternalReturned()) > $val['lastkill'])
                {
			$val['lastkill'] = intval($feedfetch->getLastInternalReturned());
		}
		$html .= "Feed: ".$val['url']."<br />\n";
		$html .= count($feedfetch->getPosted())." kills were posted and ".
			count($feedfetch->getSkipped())." were skipped"
                         . " (".$feedfetch->getNumberOfKillsFetched()." kills fetched)<br />\n";
		$html .= "Last kill ID returned was ".$val['lastkill']."<br />\n";
		if ($feedfetch->getParseMessages()) {
			$html .= implode("<br />", $feedfetch->getParseMessages());
		}
	} else {
		$html .= "Error reading feed: ".$val['url'];
		if(!$val['lastkill']) $html .= ", Start time = ".(time() - 60*60*24*7);
		$html .= $feedfetch->errormsg();
	}
	return $html."\n";
}