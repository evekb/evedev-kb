#!/usr/bin/env php
<?php
/**
 * Cron Job for importing kills from other EDK instances.
 * Fetches kills from all configured IDFeeds.
 * 
* Recommended frequency: once to four times per hour (depends on configured number of kills to fetch per run)
 * @package EDK
 */

// include the base class providing all context data
require_once('cron.base.php');

logCron("Starting IDFeed Import");
$cronStartTime = microtime(true);

// load mods
$page = 'idfeedsyndication';
event::setCron(TRUE);
loadMods($page);

// get all configured feeds
$feeds = config::get("fetch_idfeeds");

foreach($feeds as $feedId => &$feedConfig) 
{
        getIDFeed($feedId, $feedConfig);
}

logCron("Time taken = ".(microtime(true) - $cronStartTime)." seconds");


function getIDFeed(&$key, &$val)
{
        logCron("Fetching IDFeed: ".$key);
	// Just in case, check for empty urls.
	if(empty($val['url'])) 
        {
                logCron('No URL given for IDFeed, skipping');
                return;
	}
        
	$feedfetch = new IDFeed();
	$feedfetch->setID();
	$feedfetch->setAllKills(1);

	if(!$val['lastkill']) 
        {
                // if no last kill ID is given, start 7 days before today
		$feedfetch->setStartDate(time() - 60*60*24*7);
	}
        
        else 
        {
		$feedfetch->setStartKill($val['lastkill'] + 1, true);
	}

	if($feedfetch->read($val['url']) !== false) 
        {
                if(intval($feedfetch->getLastInternalReturned()) > $val['lastkill'])
                {
			$val['lastkill'] = intval($feedfetch->getLastInternalReturned());
		}
		logCron("Feed: ".$val['url']);
		logCron(count($feedfetch->getPosted())." kills were posted and ".
			count($feedfetch->getSkipped())." were skipped"
                         . " (".$feedfetch->getNumberOfKillsFetched()." kills fetched)");
		logCron("Last kill ID returned was ".$val['lastkill']);
		
                // log errors
                if ($feedfetch->getParseMessages()) 
                {
			foreach($feedfetch->getParseMessages() AS $parseMessage)
                        {
                            logCron($parseMessage);
                        }
		}
	} 
        
        else 
        {
		$logText .= "Error reading feed: ".$val['url'];
		if(!$val['lastkill'])
                {
                    $logText .= ", Start time = ".(time() - 60*60*24*7);
                }
                logCron($logText);
		logCron(
                        $feedfetch->errormsg());
	}
}