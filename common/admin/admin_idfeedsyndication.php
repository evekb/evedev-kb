<?php
/**
 * $Date: 2010-06-04 23:26:29 +1000 (Fri, 04 Jun 2010) $
 * $Revision: 774 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/admin/admin_feedsyndication.php $
 * @package EDK
 */

/*
 * EDK IDFeed Syndication v0.90
 *
 */

require_once('common/admin/admin_menu.php');

$page = new Page("Administration - IDFeed Syndication " . IDFeed::version);
$page->setCachable(false);
$page->setAdmin();

$feeds = config::get("fetch_idfeeds");
// Add an empty feed to the list, or create with one empty feed.
if(is_null($feeds)) {
	$feeds[] = array('url'=>"", 'apikills'=>0, 'trusted'=>0, 'lastkill'=>0);
	config::set("fetch_idfeeds", $feeds);
} else {
	$feeds[] = array('url'=>"", 'apikills'=>0, 'trusted'=>0, 'lastkill'=>0);
}

$feedcount = count($feeds);

// saving urls and options
if ($_POST['submit'] || $_POST['fetch'])
{
	if(is_null($feeds)) {
		$feeds = array();
	}
    foreach($feeds as $key => &$val) {
		// Use the md5 of the url as a key for each feed.
        $url = md5($val['url']);

        if ($_POST[$url]) {
            if ($_POST['trusted'] && in_array ($url, $_POST['trusted'])) {
				$val['trusted'] = 1;
			} else {
				$val['trusted'] = 0;
			}
			$val['apikills'] = 0;
			if($_POST['lastkill'.$url] != $val['lastkill']) {
				$val['lastkill'] = intval($_POST['lastkill'.$url]);
			}
            // reset the feed lastkill details if the URL or api status has changed
            if($_POST[$url] != $val['url'] ) {
				$val['url'] = $_POST[$url];
				$val['lastkill'] = 0;
			}
			if ($_POST['delete'] && in_array ($url, $_POST['delete'])) {
				unset($feeds[$key]);
			}
        } else {
			unset($feeds[$key]);
		}
    }
	$newlist = array();
	foreach($feeds as $key => &$val) {
		if ($val['url']) {
			$newlist[$val['url']] = $val;
		}
	}
	$feeds = &$newlist;
	config::set("fetch_idfeeds", $feeds);
	$feeds[] = array('url'=>"", 'apikills'=>0, 'trusted'=>0, 'lastkill'=>0);
}

// building the request query and fetching of the feeds
if ($_POST['fetch'])
{
    foreach($feeds as $key => &$val)
    {
		if(!($_POST['fetch_feed'] && in_array (md5($val['url']), $_POST['fetch_feed']))
			|| empty($val['url'])) continue;

		if (isIDFeed($val['url'])) {
			$html .= getIDFeed($key, $val);
		} else {
			$html .= getOldFeed($key, $val);
		}
		config::set("fetch_idfeeds", $feeds);
	}
}
// generating the html
$rows = array();
foreach($feeds as $key => &$val) {
	$key = md5($val['url']);
    if (!isset($_POST['fetch_feed'][$key])
			|| $_POST['fetch_feed'][$key]) {
		$fetch=false;
	} else {
		$fetch = true;
	}
	$rows[] = array('name'=>$key, 'uri'=>$val['url'], 'lastkill'=>$val['lastkill'], 'trusted'=>$val['trusted'], 'fetch'=>!$fetch);
}
$smarty->assignByRef('rows', $rows);

$smarty->assign('results', $html);
$page->addContext($menubox->generate());
$page->setContent($smarty->fetch(get_tpl('admin_idfeed')));
$page->generate();

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
		return 'No URL given<br />';
	}
	$feedfetch = new IDFeed();
	$feedfetch->setID();
	$feedfetch->setAllKills(1);
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
		$html .= "IDFeed: ".$val['url']."<br />\n";
		$html .= count($feedfetch->getPosted())." kills were posted and ".
						count($feedfetch->getSkipped())." were skipped.<br />\n";
		if ($feedfetch->getParseMessages()) {
			$html .= implode("<br />", $feedfetch->getParseMessages());
		}
	} else {
		$html .= "Error reading feed: ".$val['url'];
		if (!$val['lastkill']) {
			$html .= ", Start time = ".(time() - 60 * 60 * 24 * 7);
		} else if ($val['apikills']) {
			$html .= ", Start kill = ".($val['lastkill']);
		}
		$html .= $feedfetch->errormsg();
	}
	return $html;
}

/**
 * Check if this is an IDFeed.
 * The url parameter is modified if needed to refer directly to the IDFeed.
 * @param string $url
 * @return string HTML describing the fetch result.
 */
function isIDFeed(&$url)
{
	// If the url has idfeed or p=ed_feed in it then assume the URL is correct
	// and return immediately.
	if (strpos($url, 'idfeed')) {
		// Believe the user ...
		return true;
	} else if (strpos($url, 'p=ed_feed')) {
		// Griefwatch feed.
		return false;
	}

	// With no extension standard EDK will divert the idfeed fetcher to the idfeed
	if(strpos($url, '?') === false) {
		$urltest = $url.'?kll_id=-1';
		if (checkIDFeed($urltest)) {
			return true;
		}
	}

	// Either the bare url didn't work or we don't have a bare url.
	// Either add 'a=idfeed' to the url or change 'a=feed'.
	// If we find an idfeed then make the url change permanent and return true
	// Otherwise we have an old feed, return false.
	if(strpos($url, '?')) {
		$urltest = preg_replace('/\?.*/', '?a=idfeed&kll_id=-1', $url);
	} else if (substr($url, -1) == '/') {
		$urltest = $url."?a=idfeed&kll_id=-1";
	} else {
		$urltest = $url."/?a=idfeed&kll_id=-1";
	}
	if (checkIDFeed($urltest)) {
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
	$html = 'RSS Feed: ';
	// Just in case, check for empty urls.
	if(empty($val['url'])) return 'No URL given<br />';

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
			//TODO: Put some methods into the fetcher to get this more neatly.
			$html .= preg_replace('/(<div class=\'block-header2\'>|<\/div>)/',
				'', $feedfetch->grab($urltmp, $myid, $val['trust']))."\n";
			if(intval($feedfetch->lastkllid_) < $lastkill || !$lastkill)
					$lastkill = intval($feedfetch->lastkllid_);
			// Check if feed used combined list. get losses if not
			if(!$feedfetch->combined_) {
				$html .= preg_replace('/(<div class=\'block-header2\'>|<\/div>)/',
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
				$html .= preg_replace('/(<div class=\'block-header2\'>|<\/div>)/',
					'', $feedfetch->grab($url . "&year=" . $year . "&week=" . $l,
						$myid, $val['trust'])) . "\n";
				if(intval($feedfetch->lastkllid_) < $lastkill
						|| !$lastkill) {
					$lastkill = intval($feedfetch->lastkllid_);
				}
				$html .= preg_replace('/(<div class=\'block-header2\'>|<\/div>)/',
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

/**
 * @param string $url 
 * @return boolean True if this is an IDFeed, false if not.
 */
function checkIDFeed( $url) {
	$http = new http_request($url);
	$http->set_useragent("EDK IDFeedfetcher Check");
	$http->set_timeout(0.5);
	$res = $http->get_content();
	if ($http->status['timed_out']) {
		return false;
	} else if ($res && strpos($res, 'edkapi')) {
		return true;
	}
	return false;
}