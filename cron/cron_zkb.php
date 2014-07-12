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

// Has to be run from the KB main directory for nested includes to work
if(file_exists(getcwd().'/cron_zkb.php')) {
	// current working directory minus last 5 letters of string ("/cron")
	$KB_HOME = preg_replace('/[\/\\\\]cron$/', '', getcwd());
} else if(file_exists(__FILE__)) {
	$KB_HOME = preg_replace('/[\/\\\\]cron[\/\\\\]cron_feed\.php$/', '', __FILE__);
} else {
	echo "Set \$KB_HOME to the killboard root in cron/cron_zkb\.php.";
	die;
}

// If the above doesn't work - place your working directory path to killboard root below - comment out the above two lines and uncomment the two below

// Edit the path below with your webspace directory to the killboard root folder - also check your php folder is correct as defined by the first line of this file
//$KB_HOME = "/home/yoursite/public_html/kb";

chdir($KB_HOME);

require_once('kbconfig.php');
require_once('globals.php');
$config = new Config(KB_SITE);

$fetchConfigs = ZKBFetch::getAll();
$html = '';

foreach($fetchConfigs AS &$fetchConfig)
{
        $html .= getZKBApi($fetchConfig);
}

echo $html."<br />\n";

echo "Time taken = ".(microtime(true) - $cronStartTime)." seconds.\n";


function getZKBApi(&$fetchConfig)
{
	$html = '';
	// Just in case, check for empty urls.
	if(is_null($fetchConfig->getUrl())) 
        {
            return 'No URL given<br />';
	}
	
        if(!$fetchConfig->getLastKillTimestamp())
        {
            $fetchConfig->setLastKillTimestamp(time() - 60 * 60 * 24 * 7);
        }
        
        try
        {
            $fetchConfig->setMaxNumberOfKillsPerCycle(config::get('maxNumberOfKillsPerCycle'));
            $fetchConfig->setIgnoreNpcOnlyKills(config::get('post_no_npc_only_zkb'));
            $fetchConfig->processApi();
            $html .= "ZKBApi: ".$fetchConfig->getUrl()."<br />\n";
            $html .= count($fetchConfig->getPosted())." kills were posted and ".
						count($fetchConfig->getSkipped())." were skipped. ";
            $html .= "Timestamp of last kill: ".strftime('%Y-%m-%d %H:%M:%S', $fetchConfig->getLastKillTimestamp());
            $html .= "<br />\n";
            if ($fetchConfig->getParseMessages()) 
            {
                $html .= implode("<br />", $fetchConfig->getParseMessages());
            }
        } 
        
        catch (Exception $ex) 
        {
            $html .= "Error reading feed: ".$fetchConfig->getUrl()."<br/>";
            $lastKillTimestampFormatted = strftime('%Y-%m-%d %H:%M:%S', $fetchConfig->getLastKillTimestamp());
            $html .= $ex->getMessage();
            $html .= ", Start time = ".$lastKillTimestampFormatted;
            $html .= "<br/><br/>";

        }
	
	return $html;
}
