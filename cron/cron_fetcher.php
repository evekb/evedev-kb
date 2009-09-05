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

@set_time_limit(0);

$cronStartTime = microtime(true);

// Has to be run from the KB main directory for nested includes to work
$KB_HOME = preg_replace('/[\/\\\\]cron$/', '', getcwd());
chdir($KB_HOME);

require_once('kbconfig.php');
require_once('common/includes/globals.php');
require_once('common/includes/class.config.php');
require_once('common/includes/class.corp.php');
require_once('common/includes/class.alliance.php');
require_once('common/includes/class.event.php');
require_once('common/admin/feed_fetcher.php');
require_once('common/includes/db.php');

$config = new Config(KB_SITE);

$validurl = "/^(http|https):\/\/([A-Za-z0-9_]+(:[A-Za-z0-9_]+)?@)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}((:[0-9]{1,5})?\/.*)?$/i";

// load the config from the database
if (config::get('fetch_feed_count'))
    $feedcount = config::get('fetch_feed_count');
else
    $feedcount = 3;

// corporation OR alliance id
if (config::get('cfg_corpid'))
{
    define('CORP_ID', intval(config::get('cfg_corpid')));
    define('ALLIANCE_ID', 0);
}
else
{
    define('CORP_ID', 0);
    define('ALLIANCE_ID', intval(config::get('cfg_allianceid')));
}

if (CORP_ID && !MASTER)
{
    $corp = new Corporation(CORP_ID);
    $myid = '&corp=' . urlencode($corp->getName());
}

if (ALLIANCE_ID && !MASTER)
{
    $alli = new Alliance(ALLIANCE_ID);
    $myid = '&alli=' . urlencode($alli->getName());
}
define('KB_TITLE', config::get('cfg_kbtitle'));
define('DB_USE_CCP', true);

$year = gmdate("Y");
$week = gmdate("W");

$outhead = "Running on " . gmdate(DATE_RFC822) . "\n";
$out = '';

$feed = array();
$friend = array();
for ($i = 1; $i <= $feedcount; $i++)
{
    $str = config::get('fetch_url_' . $i);
    $tmp = explode(':::', $str);
    $feed[$i] = $tmp[0];
    $feedlast[$i] = intval($tmp[1]);
    if ($tmp[2] == "on")
        $friend[$i] = $tmp[2];
	if ($tmp[3] == "on")
        $apikills[$i] = $tmp[3];
    $feedfetch = new Fetcher();
    if (preg_match($validurl , $feed[$i]))
    {
        $str = '';
        if ($feedlast[$i])
        {
            $str .= '&combined=1';
            $str .= '&lastkllid='.$feedlast[$i];
        }
        if ($friend[$i])
            $str .= '&friend=1';
		if ($apikills[$i])
			$str .= '&apikills=1';
        if (!config::get('fetch_compress'))
            $str .= "&gz=1";
        // If a last kill id is specified fetch all kills since then
        if($feedlast[$i] > 0)
        {
            $out .= preg_replace('/<div.+No kills added from feed.+<\/div>/','',$feedfetch->grab($feed[$i], $myid . $str, $friend[$i], "fetch_url_" . $i)). "\n";
            if(intval($feedfetch->lastkllid_)) $feedlast[$i] = intval($feedfetch->lastkllid_);
            // Check if feed used combined list. get losses if not
            if(!$feedfetch->combined_)
            {
                    $out .= preg_replace('/<div.+No kills added from feed.+<\/div>/','',$feedfetch->grab($feed[$i], $myid . $str . "&losses=1", $friend[$i], "fetch_url_" . $i)) . "\n";
                    if(intval($feedfetch->lastkllid_)) $feedlast[$i] = intval($feedfetch->lastkllid_);
            }
            // Store most recent kill id fetched
            if($feedfetch->lastkllid_)
            {
                //Fetch final kill id of board from feed if possible and set as new last kill.
                if(intval($feedfetch->finalkllid_)> $feedlast[$i])
                    config::set("fetch_url_" . $i, $feed[$i] . ':::' . intval($feedfetch->finalkllid_) . ':::' . $friend[$i]);
                else config::set("fetch_url_" . $i, $feed[$i] . ':::' . $feedlast[$i] . ':::' . $friend[$i]);
            }
        }
        // If no last kill is specified then fetch by week
        else
        {
                // Fetch for current and previous weeks, both kills and losses
                for ($l = $week - 1; $l <= $week; $l++)
                {
                    $out .= preg_replace('/<div.+No kills added from feed.+<\/div>/','',$feedfetch->grab($feed[$i] . "&year=" . $year . "&week=" . $l, $myid . $str)). "\n";
                        if(intval($feedfetch->lastkllid_)) $feedlast[$i] = intval($feedfetch->lastkllid_);
                    $out .= preg_replace('/<div.+No kills added from feed.+<\/div>/','',$feedfetch->grab($feed[$i] . "&year=" . $year . "&week=" . $l, $myid . $str . "&losses=1")) . "\n";
                        if(intval($feedfetch->lastkllid_ )) $feedlast[$i] = intval($feedfetch->lastkllid_);
                }
                // Store most recent kill id fetched
                if($feedfetch->lastkllid_) config::set("fetch_url_" . $i, $feed[$i] . ':::' . $feedlast[$i] . ':::' . $friend[$i]);
        }
    }
}

if ($out)
{
//    print $outhead . strip_tags(str_replace("</div>","\n",$out), '<a>');
    print $outhead . strip_tags($out);
}

echo "Time taken = ".(microtime(true) - $cronStartTime)." seconds.";
