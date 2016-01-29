#!/usr/bin/env php
<?php
/**
 * Cron Job for importing kills from zKB APIs
 * Imports kills from all configured zKB Fetch URLs.
 * 
 * Recommended frequency: once to four times per hour (depends on configured number of kills to fetch per run)
 * 
 * @package EDK
 */

// include the base class providing all context data
require_once('cron.base.php');

$cronStartTime = microtime(true);
logCron("Starting zKB Import");

// load mods
$page = 'zkbfetch';
event::setCron(TRUE);
loadMods($page);

// get all zKB Fetch configurations
$fetchConfigs = ZKBFetch::getAll();
$html = '';

foreach($fetchConfigs AS &$fetchConfig)
{
    
    getZKBApi($fetchConfig);
}


logCron('Time taken = '.(microtime(true) - $cronStartTime).' seconds.');


function getZKBApi(&$fetchConfig)
{
	// Just in case, check for empty urls.
	if(is_null($fetchConfig->getUrl())) 
        {
            log('No URL given for zKBFetch, skipping');
            return;
	}
	
        // if there is now timestamp to start fetching from,
        // set it to begin 7 days before this day
        if(!$fetchConfig->getLastKillTimestamp())
        {
            $fetchConfig->setLastKillTimestamp(time() - 60 * 60 * 24 * 7);
        }
        
        try
        {
            $fetchConfig->setKillTimestampOffset(config::get('killTimestampOffset'));
            $fetchConfig->setIgnoreNpcOnlyKills((boolean)(config::get('post_no_npc_only_zkb')));
            $fetchConfig->processApi();
            logCron("ZKBApi: ".$fetchConfig->getUrl());
            logCron(count($fetchConfig->getPosted())." kills were posted and ".count($fetchConfig->getSkipped())." were skipped "
                                                . "(".$fetchConfig->getNumberOfKillsFetched()." kills fetched)");
            logCron("Timestamp of last kill: ".strftime('%Y-%m-%d %H:%M:%S', $fetchConfig->getLastKillTimestamp()));
            if ($fetchConfig->getParseMessages()) 
            {
                foreach($fetchConfig->getParseMessages() AS $parseMessage)
                {
                    logCron($parseMessage);
                }
            }
        } 
        
        catch (Exception $ex) 
        {
            logCron("Error reading feed: ".$fetchConfig->getUrl());
            $lastKillTimestampFormatted = strftime('%Y-%m-%d %H:%M:%S', $fetchConfig->getLastKillTimestamp());
            logCron($ex->getMessage().", Start time = ".$lastKillTimestampFormatted);
        }
}
