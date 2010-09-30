#!/usr/bin/php
<?php
/*
 * EDK Feed Syndication v1.7
 * based on liq's feed syndication mod v1.5
 *
 */

// set this to 1 if you are running a master killboard and want
// to even fetch mails not related to your corp / alliance
define('MASTER', 0);
@error_reporting(E_ERROR);
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

// Has to be run from the KB main directory for nested includes to work
if(file_exists(getcwd().'/cron_fetcher.php'))
{
	// current working directory minus last 5 letters of string ("/cron")
	$KB_HOME = preg_replace('/[\/\\\\]cron$/', '', getcwd());
}
elseif(file_exists(__FILE__))
{
	$KB_HOME = preg_replace('/[\/\\\\]cron[\/\\\\]cron_fetcher\.php$/', '', __FILE__);
}
else die("Set \$KB_HOME to the killboard root in cron/cron_fetcher.php.");

// If the above doesn't work - place your working directory path to killboard root below - comment out the above two lines and uncomment the two below

// Edit the path below with your webspace directory to the killboard root folder - also check your php folder is correct as defined by the first line of this file
//$KB_HOME = "/home/yoursite/public_html/kb";

chdir($KB_HOME);

require_once('kbconfig.php');
require_once('common/includes/class.edkloader.php');
require_once('common/includes/globals.php');
require_once('common/includes/db.php');

$config = new Config(KB_SITE);

$validurl = "/^(http|https):\/\/([A-Za-z0-9_]+(:[A-Za-z0-9_]+)?@)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*((:[0-9]{1,5})?\/.*)?$/i";

// load the config from the database
if (config::get('fetch_feed_count'))
    $feedcount = config::get('fetch_feed_count');
else
    $feedcount = 1;

$myid = getID();

define('KB_TITLE', config::get('cfg_kbtitle'));
define('DB_USE_CCP', true);

$year = gmdate("Y");
$week = gmdate("W");

$outhead = "Running on " . gmdate(DATE_RFC822) . "\n";
$out = '';

$feed = array();
$friend = array();
$apikills = array();

// Check if we have been asked to fetch a specific feed
$i = 1;
// Check query string
if(isset($_GET['feed']))
{
	$i = intval($_GET['feed']);
	if(!$i) $i = 1;
	elseif($feedcount > $i) $feedcount = $i;
}
// Check command line arguments
elseif(isset($argv[0]))
{
	foreach($argv as $arg)
	{
		if(substr($arg, 0, 5) == "feed=")
		{
			$i = intval(substr($arg,5));
			if(!$i) $i = 1;
			elseif($feedcount > $i) $feedcount = $i;
		}
	}
}

// Fetch each feed.
// Try to fetch all kills since the previous fetch. Otherwise fetch by week.
// Fetch the combined feed first. If this only returns kills then it is a <2.0
// board so do another fetch for losses.
for (; $i <= $feedcount; $i++)
{
    $str = config::get('fetch_url_' . $i);
    $tmp = explode(':::', $str);
    $feed[$i] = $tmp[0];
    $feedlast[$i] = intval($tmp[1]);
	if (isset($tmp[3]) && $tmp[3] == "on")
        $apikills[$i] = $tmp[3];
	else $apikills[$i] = "";
	if (isset($tmp[4]) && $tmp[4])
        $trusted[$i] = 1;
	else $trusted[$i] = 0;
    $feedfetch = new Fetcher();
    if (preg_match($validurl , $feed[$i]))
    {
        $str = '';
        if ($feedlast[$i])
        {
            $str .= '&combined=1';
            $str .= '&lastkllid='.$feedlast[$i];
        }
		if (($apikills[$i]))
			$str .= '&APIkills=1';
        // If a last kill id is specified fetch all kills since then
        if($feedlast[$i] > 0)
        {
            $out .= preg_replace('/<div.+No kills added from feed.+<\/div>/','',$feedfetch->grab($feed[$i], $myid . $str, $trusted[$i], "fetch_url_" . $i)). "\n";
            if(intval($feedfetch->lastkllid_)) $feedlast[$i] = intval($feedfetch->lastkllid_);
            // Check if feed used combined list. get losses if not
            if(!$feedfetch->combined_)
            {
                    $out .= preg_replace('/<div.+No kills added from feed.+<\/div>/','',$feedfetch->grab($feed[$i], $myid . $str . "&losses=1", $trusted[$i], "fetch_url_" . $i)) . "\n";
                    if(intval($feedfetch->lastkllid_) > $feedlast[$i]) $feedlast[$i] = intval($feedfetch->lastkllid_);
            }
            // Store most recent kill id fetched
            if($feedfetch->lastkllid_)
            {
                //Fetch final kill id of board from feed if possible and set as new last kill.
                if(intval($feedfetch->finalkllid_)> $feedlast[$i])
                    config::set("fetch_url_" . $i, $feed[$i] . ':::' . intval($feedfetch->finalkllid_) . ':::' . 0 . ':::' . $apikills[$i] . ':::' . $trusted[$i]);
                else config::set("fetch_url_" . $i, $feed[$i] . ':::' . $feedlast[$i] . ':::' . 0 . ':::' . $apikills[$i] . ':::' . $trusted[$i]);
            }
        }
        // If no last kill is specified then fetch by week
        else
        {
                // Fetch for current and previous weeks, both kills and losses
                for ($l = $week - 1; $l <= $week; $l++)
                {
                    $out .= preg_replace('/<div.+No kills added from feed.+<\/div>/','',$feedfetch->grab($feed[$i] . "&year=" . $year . "&week=" . $l, $myid . $str, $trusted[$i])). "\n";
                        if(intval($feedfetch->lastkllid_)) $feedlast[$i] = intval($feedfetch->lastkllid_);
                    $out .= preg_replace('/<div.+No kills added from feed.+<\/div>/','',$feedfetch->grab($feed[$i] . "&year=" . $year . "&week=" . $l, $myid . $str . "&losses=1", $trusted[$i])) . "\n";
                        if(intval($feedfetch->lastkllid_ ) > $feedlast[$i]) $feedlast[$i] = intval($feedfetch->lastkllid_);
                }
                // Store most recent kill id fetched
                if($feedfetch->lastkllid_ > $feedlast[$i]) config::set("fetch_url_" . $i, $feed[$i] . ':::' . $feedlast[$i] . ':::' . 0 . ':::' . $apikills[$i] . ':::' . $trusted[$i]);
        }
    }
}

if ($out)
{
//    print $outhead . strip_tags(str_replace("</div>","\n",$out), '<a>');
    print $outhead . strip_tags($out);
}

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

	if (PILOT_ID && !MASTER)
	{
		$pilot = new Pilot(PILOT_ID);
		$myid = '&pilot=' . urlencode($pilot->getName());
	}
	else if (CORP_ID && !MASTER)
	{
		$corp = new Corporation(CORP_ID);
		$myid = '&corp=' . urlencode($corp->getName());
	}

	else if (ALLIANCE_ID && !MASTER)
	{
		$alli = new Alliance(ALLIANCE_ID);
		$myid = '&alli=' . urlencode($alli->getName());
	}
	return $myid;
}
