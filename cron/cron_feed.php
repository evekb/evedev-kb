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

$cronStartTime = microtime(true);
@error_reporting(E_ERROR);

if( php_sapi_name() == 'cli' ) {
	ob_implicit_flush(true);
}

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

$qry = new DBQuery();
$qry->execute("SELECT * FROM kb3_feeds WHERE feed_kbsite = '".KB_SITE."'");
while ($row = $qry->getRow()) {
	$tmphtml = '';
	$id = $row["feed_id"];
	$url = $row["feed_url"];
	$trusted = (bool)($row["feed_flags"] & FEED_TRUSTED);
	$active = (bool)($row["feed_flags"] & FEED_ACTIVE);
	$lastkill = (int) $row["feed_lastkill"];

	if ( $active ) {
		printlog( "Processing Feed ($id) - " . $url);
		getIDFeed($id, $url, $trusted, $lastkill);
	} else {
		printlog( "Skipping Feed ($id) - " . $url);
	}
}

printlog("Time taken = ".(microtime(true) - $cronStartTime)." seconds.");

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

function getIDFeed($id, $url, $trusted, $lastkill)
{
	// Just in case, check for empty urls.
	if(empty($url)) {
		return '';
	}

	$qry2 = new DBQuery();
	$feedfetch = new IDFeed();
	$feedfetch->setID();
	$feedfetch->setAllKills(1);

	if ($trusted) {
		$feedfetch->setAcceptedTrust(1);
	} else {
		$feedfetch->setAcceptedTrust(0);
	}
	if(!$lastkill) {
		$feedfetch->setStartDate(time() - 60*60*24*7);
	} else {
		$feedfetch->setStartKill($lastkill + 1, true);
	}

	if($feedfetch->read($url) !== false) {
		$posted = count($feedfetch->getPosted());
		$skipped = count($feedfetch->getSkipped());
		$duplicate = count($feedfetch->getDuplicate());

		if( $posted+$skipped+$duplicate == 0 ) {
			$qry2->execute("UPDATE kb3_feeds SET feed_flags=0 WHERE feed_kbsite = '".KB_SITE."' AND feed_id = $id");
 		}
		if( intval($feedfetch->getLastInternalReturned()) > $lastkill) {
			$qry2->execute("UPDATE kb3_feeds SET feed_lastkill=".
							intval($feedfetch->getLastInternalReturned()) .", feed_updated=NOW()
							WHERE feed_kbsite = '".KB_SITE."' AND feed_id = $id");
		}
		printlog( $posted." kills were posted, ".$duplicate." duplicate kills and ".$skipped." were skipped.");
		printlog("Last kill ID returned was ".intval($feedfetch->getLastInternalReturned()));
		if ($feedfetch->getParseMessages()) {
			foreach( $feedfetch->getParseMessages() as $msg) {
				printlog($msg);
			}
 		}
 	} else {
		printlog("Error reading feed: ".$val['url']);
		printlog($feedfetch->errormsg());die;
		$qry2->execute("UPDATE kb3_feeds SET feed_flags=0 WHERE feed_kbsite = '".KB_SITE."' AND feed_id = $id");
		if(!$val['lastkill']) $html .= ", Start time = ".(time() - 60*60*24*7);
		else if($val['apikills']) $html .= ", Start kill = ".($val['lastkill']);
		printlog($feedfetch->errormsg());
	}
	return $html;
}

function printlog($string) {
	if( php_sapi_name() != 'cli' ) {
		echo $string . "<br />\n";
	} else {
		echo $string . "\n";
	}
}