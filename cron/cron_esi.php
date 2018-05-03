#!/usr/bin/env php
<?php
/**
 * Cron Job for importing kills from ESI
 * Imports kills from all registered and enabled SSO keys.
 * 
 * Recommended frequency: once to four times per hour (depends on configured number of kills to fetch per run)
 * 
 * @package EDK
 */

use EDK\ESI\ESIFetch;
use EDK\ESI\ESI;

// include the base class providing all context data
require_once('cron.base.php');

// load mods
$page = 'esisso';
event::setCron(TRUE);
loadMods($page);

$cronStartTime = microtime(true);
logCron("Starting ESI Import");

// get all zKB Fetch configurations
$fetchConfigs = ESIFetch::getAll();
$html = '';

foreach($fetchConfigs AS &$fetchConfig)
{
    getESIApi($fetchConfig);
}

logCron('Spent '.ESI::getTotalEsiTime().'s talking to ESI');
logCron('Time taken = '.(microtime(true) - $cronStartTime).' seconds.');

/**
 * 
 * @param ESIFetch $fetchConfig
 */
function getESIApi(&$fetchConfig)
{
    $Pilot = new Pilot(0, $fetchConfig->getCharacterID());
    // Just in case, check for empty urls.
    if($fetchConfig->getFailcount() >= 5) 
    {
        logCron('Skipping key for '.$Pilot->getName().' (type: '.$fetchConfig->getKeyType().'), because the last '.$fetchConfig->getFailcount().' attempts failed!');
        return;
    }
    try
    {
        $fetchConfig->setIgnoreNpcOnlyKills((boolean)(config::get('post_no_npc_only')));
        $fetchConfig->setMaximumProcessingTime((int)config::get('cfg_max_proc_time_per_sso_key'));
        $fetchConfig->processApi();
        logCron("ESI SSO Key: ".$Pilot->getName()." (type: ".$fetchConfig->getKeyType().")");
        logCron(count($fetchConfig->getPosted())." kills were posted and ".count($fetchConfig->getSkipped())." were skipped "
                                            . "(".$fetchConfig->getNumberOfKillsFetched()." kills fetched)");
        logCron("Last kill ID: ".$fetchConfig->getLastKillID());
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
        logCron("Error processing ESI API for ".$Pilot->getName()." (type: ".$fetchConfig->getKeyType().")");
        logCron($ex->getMessage());
    }
}
