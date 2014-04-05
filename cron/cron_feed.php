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

foreach($feeds as $key => &$val) {
	$tmphtml = '';
	if (isIDFeed($val['url'])) {
		if ($tmphtml = getIDFeed($key, $val)) {
			$html .= "Fetching IDFeed: ".$key."<br />\n".$tmphtml;
		}
	} else {
		if ($tmphtml = getOldFeed($key, $val)) {
			$html .= "Fetching RSS Feed: ".$key."<br />\n".$tmphtml;
		}
	}
	if ($tmphtml ) {
		config::set("fetch_idfeeds", $feeds);
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
	if($val['apikills']) {
		$feedfetch->setAllKills(0);
	} else {
		$feedfetch->setAllKills(1);
	}
	if ($val['trusted']) {
		$feedfetch->setAcceptedTrust(1);
	}
	if(!$val['lastkill']) {
		$feedfetch->setStartDate(time() - 60*60*24*7);
	} else if($val['apikills']) {
		$feedfetch->setStartKill($val['lastkill'] + 1);
	} else {
		$feedfetch->setStartKill($val['lastkill'] + 1, true);
	}

	if($feedfetch->read($val['url']) !== false) {
		if($val['apikills'] 
				&& intval($feedfetch->getLastReturned()) > $val['lastkill']) {
			$val['lastkill'] = intval($feedfetch->getLastReturned());
		} else if(!$val['apikills']
				&& intval($feedfetch->getLastInternalReturned())
						> $val['lastkill']) {
			$val['lastkill'] = intval($feedfetch->getLastInternalReturned());
		}
		$html .= "Feed: ".$val['url']."<br />\n";
		$html .= count($feedfetch->getPosted())." kills were posted and ".
			count($feedfetch->getSkipped())." were skipped.<br />\n";
		$html .= "Last kill ID returned was ".$val['lastkill']."<br />\n";
		if ($feedfetch->getParseMessages()) {
			$html .= implode("<br />", $feedfetch->getParseMessages());
		}
	} else {
		$html .= "Error reading feed: ".$val['url'];
		if(!$val['lastkill']) $html .= ", Start time = ".(time() - 60*60*24*7);
		else if($val['apikills']) $html .= ", Start kill = ".($val['lastkill']);
		$html .= $feedfetch->errormsg();
	}
	return $html."\n";
}

/**
 * Check if this is an IDFeed.
 * The url parameter is modified if needed to refer directly to the IDFeed.
 * @param string $url
 * @return string HTML describing the fetch result.
 */
function isIDFeed(&$url)
{
	if (!$url) {
		// No point checking further.
		return false;
	} else if (strpos($url, 'idfeed')) {
		// Believe the user ...
		return true;
	}

	if(strpos($url, '?')) {
		$urltest = preg_replace('/\?.*/', '?a=idfeed&kll_id=-1', $url);
	} else if (substr($url, -1) == '/') {
		$urltest = $url."?a=idfeed&kll_id=-1";
	} else {
		$urltest = $url."/?a=idfeed&kll_id=-1";
	}
	$http = new http_request($urltest);
	$http->set_useragent("EDK IDFeedfetcher Check");
	$http->set_timeout(10);
	$res = $http->get_content();
	if ($res && strpos($res, 'edkapi')) {
		if(strpos($url, '?a=feed')) {
			$url = preg_replace('/\?a=feed/', '?a=idfeed', $url);
		} else if(strpos($url, '?')) {
			$url = preg_replace('/\?/', '?a=idfeed&', $url);
		} else if (substr($url, -1) == '/') {
			$url = $url."?a=idfeed";
		} else {
			$url = $url."/?a=idfeed";
		}
		return true;
	} else {
		return false;
	}
}

function getOldFeed(&$key, &$val)
{
	$html = '';
	// Just in case, check for empty urls.
	if(empty($val['url'])) {
		return '';
	}

	$url = $val['url'];
	if (!strpos($url, 'a=feed')) {
		if (strpos($url, '?')) {
			$url = str_replace('?', '?a=feed&', $url);
		} else {
			$url .= "?a=feed";
		}
	}
	$feedfetch = new Fetcher();

	$myids = getOwners();
	$lastkill = 0;
	foreach($myids as $myid) {
		// If a last kill id is specified fetch all kills since then
		if($val['lastkill'] > 0) {
			$urltmp = $url.'&combined=1&lastkllid='.$val['lastkill'];
			$html .= preg_replace('/<div.+No kills added from feed.+<\/div>/',
				'', $feedfetch->grab($urltmp, $myid, $val['trust']))."\n";
			if(intval($feedfetch->lastkllid_) < $lastkill || !$lastkill)
					$lastkill = intval($feedfetch->lastkllid_);
			// Check if feed used combined list. get losses if not
			if(!$feedfetch->combined_) {
				$html .= preg_replace('/<div.+No kills added from feed.+<\/div>/',
					'', $feedfetch->grab($urltmp, $myid."&losses=1", $val['trust']))."\n";
				if(intval($feedfetch->lastkllid_) < $lastkill || !$lastkill)
						$lastkill = intval($feedfetch->lastkllid_);
			}
			// Store most recent kill id fetched
			if($lastkill > $val['lastkill']) {
				$val['lastkill'] = $lastkill;
			}
		} else {
			// If no last kill is specified then fetch by week
			// Fetch for current and previous weeks, both kills and losses
			for($l = $week - 1; $l <= $week; $l++)
			{
				$html .= preg_replace('/<div.+No kills added from feed.+<\/div>/',
					'', $feedfetch->grab($url . "&year=" . $year . "&week=" . $l,
						$myid, $val['trust'])) . "\n";
				if(intval($feedfetch->lastkllid_) < $lastkill
						|| !$lastkill) {
					$lastkill = intval($feedfetch->lastkllid_);
				}
				$html .= preg_replace('/<div.+No kills added from feed.+<\/div>/',
					'', $feedfetch->grab($url . "&year=" . $year . "&week=" . $l,
						$myid . "&losses=1", $val['trust'])) . "\n";
				if(intval($feedfetch->lastkllid_) < $lastkill
						|| !$lastkill) {
					$lastkill = intval($feedfetch->lastkllid_);
				}
			}
			// Store most recent kill id fetched
			if($lastkill > $val['lastkill']) {
				$val['lastkill'] = $lastkill;
			}
		}
	}
	return $html;
}
