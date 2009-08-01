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
$KB_HOME = ereg_replace('[/\\]cron$', '', getcwd());

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

define('KB_TITLE', config::get('cfg_kbtitle'));

// corporation OR alliance id
if (config::get('cfg_corpid'))
{
    define('CORP_ID', intval(config::get('cfg_corpid')));
    define('ALLIANCE_ID', 0);
} else {
    define('CORP_ID', 0);
    define('ALLIANCE_ID', intval(config::get('cfg_allianceid')));
}

if (!$dir = config::get('cache_dir'))
{
    $dir = 'cache/data';
}
define('KB_CACHEDIR', $dir);

$outhead = "Running API Mod ". APIVERSION . " on " . gmdate("M d Y H:i") . "\n\n";
$out = '';

$myEveAPI = new API_KillLog();
$myEveAPI->iscronjob_ = true;

$keycount = config::get('API_Key_count');

for ($i = 1; $i <= $keycount; $i++)
{
    $keyindex = $i;
    $myEveAPI->Output_ = "Importing Mails for " . $config->get("API_Name_" . $i);
	$myEveAPI->Output_ .= "\n";
    $typestring = $config->get("API_Type_" . $i);
    $keystring = 'userID=' . $config->get('API_UserID_' . $i) . '&apiKey=' . $config->get('API_Key_' . $i) . '&characterID=' . $config->get('API_CharID_' . $i);
	$myEveAPI->cachetext_ = "";
	$myEveAPI->cacheflag_ = false;
    $outtemp .= $myEveAPI->Import($keystring, $typestring, $keyindex);
	//config::set('API_CachedUntil_' . $keyindex, $myEveAPI->cachetext_);
}
$out = $outtemp;

if ($out)
{
    $out = str_replace("<div class=block-header2>","",$out);
    $out = str_replace("</div>","\n",$out);
    $out = str_replace("<br>","\n",$out);

    //print $outhead . strip_tags($out, '<a>');
    print $outhead . strip_tags($out);
}
?>
