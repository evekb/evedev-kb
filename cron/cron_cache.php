#!/usr/bin/php 
<?php
// check your php folder is correct as defined by the first line of this file
@error_reporting(E_ALL ^ E_NOTICE);
//
// Simple Cronjob script - set it to run this, no more than once an hour as you can only pull info once an hour anyway
// by Captain Thunk! (ISK donations are all gratefully received)
//

if(function_exists("set_time_limit"))
	set_time_limit(0);

// current working directory minus last 5 letters of string ("/cron")
//$KB_HOME = substr(getcwd(), 0, strlen(getcwd())-5); // current working directory minus last 5 letters of string ("/cron")
$KB_HOME = preg_replace('/[\/\\\\]cron$/', '', getcwd());


chdir($KB_HOME); 

// If the above doesn't work - place your working directory path to killboard root below - comment out the above two lines and uncomment the two below

// Edit the path below with your webspace directory to the killboard root folder - also check your php folder is correct as defined by the first line of this file
//$KB_HOME = "/home/yoursite/public_html/kb";
//chdir($KB_HOME); 

require_once( "kbconfig.php" );
require_once( "common/includes/class.config.php" );
require_once( "common/includes/class.apicache.php" );
require_once( "common/includes/class.event.php" );
require_once( "common/includes/globals.php" );
require_once( "common/includes/class.eveapi.php" );
require_once( "common/includes/db.php" );

$config = new Config(KB_SITE);
$ApiCache = new ApiCache(KB_SITE);

$outhead = "Running Cron_Cache - with API Mod ". APIVERSION . " on " . gmdate("M d Y H:i") . "\n\n";
$out = '';

// Sovereignty
$mySovAPI = new API_Sovereignty();
$Sovtemp = $mySovAPI->fetchXML();
$out .= "Caching Sovereignty XML - cached until:" . ConvertTimestamp($mySovAPI->CachedUntil_) . "\n";

// Alliance
$myAlliAPI = new AllianceAPI();
$Allitemp .= $myAlliAPI->initXML();
$out .= "Caching Alliance XML - cached until:" . ConvertTimestamp($myAlliAPI->CachedUntil_) . "\n";

// Conquerable Station and Outposts list
$myConqAPI = new API_ConquerableStationList();
$Conqtemp .= $myConqAPI->fetchXML();
$out .= "Caching Conquerable Station XML - cached until:" . ConvertTimestamp($myConqAPI->CachedUntil_) . "\n";

// Factional Warfare Systems
$myFacAPI = new API_FacWarSystems();
$Factemp .= $myFacAPI->fetchXML();
$out .= "Caching Factional Warfare Systems XML - cached until:" . ConvertTimestamp($myFacAPI->CachedUntil_) . "\n";

if ($out)
{
    $out = str_replace("<div class=block-header2>","",$out);
    $out = str_replace("</div>","\n",$out);
    $out = str_replace("<br>","\n",$out);

    //print $outhead . strip_tags($out, '<a>');
    print $outhead . strip_tags($out);
}
?>
